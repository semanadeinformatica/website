<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\EnrollmentProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ShopAdminController extends Controller
{
    public function purchases(Request $request): Response
    {
        /** @var Edition|null $currentEdition */
        $currentEdition = $request->input('edition');

        $editions = Edition::query()
            ->select('id', 'name', 'year', 'shop_enabled')
            ->orderByDesc('year')
            ->get();

        $requestedEditionId = $request->query('edition_id');

        if ($requestedEditionId === 'all') {
            $editionId = null;
        } elseif (is_int($requestedEditionId) || (is_string($requestedEditionId) && ctype_digit($requestedEditionId))) {
            $editionId = (int) $requestedEditionId;
        } else {
            $editionId = $currentEdition?->id;
        }

        $search = trim((string) $request->query('query', ''));

        $purchases = EnrollmentProduct::query()
            ->with([
                'product',
                'enrollment.participant.user',
            ])
            ->when($editionId !== null, fn ($query) => $query
                ->whereHas('product', fn ($productQuery) => $productQuery->where('edition_id', $editionId)))
            ->when($search !== '', function ($query) use ($search) {
                $operator = $query->getModel()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $term = '%'.$search.'%';

                $query->where(function ($query) use ($operator, $term) {
                    $query->whereHas('product', fn ($productQuery) => $productQuery->where('name', $operator, $term))
                        ->orWhereHas('enrollment.participant.user', function ($userQuery) use ($operator, $term) {
                            $userQuery->where('name', $operator, $term)
                                ->orWhere('email', $operator, $term);
                        });
                });
            })
            ->orderByDesc('enrollment_product.enrollment_id')
            ->orderBy('enrollment_product.product_id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (EnrollmentProduct $purchase) => [
                'enrollment_id' => $purchase->enrollment_id,
                'product_id' => $purchase->product_id,
                'redeemed' => (bool) $purchase->redeemed,
                'price' => $purchase->price !== null
                    ? (int) $purchase->price
                    : (int) ($purchase->product?->price ?? 0),
                'product_name' => $purchase->product?->name,
                'product_stock' => $purchase->product?->stock,
                'participant_name' => $purchase->enrollment?->participant?->user?->name,
                'participant_email' => $purchase->enrollment?->participant?->user?->email,
            ]);

        $summaryQuery = DB::table('enrollment_product')
            ->join('products', 'products.id', '=', 'enrollment_product.product_id')
            ->when($editionId !== null, fn ($query) => $query->where('products.edition_id', $editionId));

        $summary = [
            'purchases' => (clone $summaryQuery)->count(),
            'participants' => (clone $summaryQuery)->distinct()->count('enrollment_product.enrollment_id'),
            'points' => (int) (clone $summaryQuery)->sum(DB::raw('COALESCE(enrollment_product.price, products.price)')),
            'pending' => (clone $summaryQuery)->where('enrollment_product.redeemed', false)->count(),
        ];

        return Inertia::render('Admin/ShopPurchases', [
            'purchases' => $purchases,
            'summary' => $summary,
            'filters' => [
                'edition_id' => $editionId === null ? 'all' : (string) $editionId,
                'query' => $search,
            ],
            'editions' => $editions,
            'currentEdition' => $currentEdition === null ? null : [
                'id' => $currentEdition->id,
                'name' => $currentEdition->name,
                'shop_enabled' => $currentEdition->shop_enabled,
            ],
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        /** @var Edition|null $edition */
        $edition = $request->input('edition');

        if ($edition === null) {
            return redirect()->back()->dangerBanner('Não foi possível identificar a edição atual.');
        }

        $edition->update(['shop_enabled' => ! $edition->shop_enabled]);

        Log::info('Shop availability changed', [
            'admin_id' => $request->user()?->id,
            'edition_id' => $edition->id,
            'shop_enabled' => $edition->shop_enabled,
        ]);

        return redirect()->back()->banner(
            $edition->shop_enabled
                ? 'Loja ativada. Os participantes já podem voltar a comprar.'
                : 'Loja desativada. Os participantes deixam de conseguir ver a loja e comprar.'
        );
    }

    public function refund(Request $request, Product $product, Enrollment $enrollment): RedirectResponse
    {
        if ((int) $product->edition_id !== (int) $enrollment->edition_id) {
            abort(404);
        }

        $refundedPoints = DB::transaction(function () use ($product, $enrollment) {
            $lockedEnrollment = Enrollment::query()->lockForUpdate()->find($enrollment->id);
            $lockedProduct = Product::query()->lockForUpdate()->find($product->id);

            if ($lockedProduct === null || $lockedEnrollment === null) {
                return null;
            }

            $purchase = EnrollmentProduct::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($purchase === null) {
                return null;
            }

            $refund = (int) ($purchase->price ?? $lockedProduct->price);

            $lockedEnrollment->points += $refund;
            $lockedEnrollment->save();

            $lockedProduct->stock += 1;
            $lockedProduct->save();

            EnrollmentProduct::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('product_id', $product->id)
                ->delete();

            return $refund;
        });

        if ($refundedPoints === null) {
            return redirect()->back()->dangerBanner('Essa compra já não existe.');
        }

        Log::info('Shop purchase refunded and removed', [
            'admin_id' => $request->user()?->id,
            'enrollment_id' => $enrollment->id,
            'product_id' => $product->id,
            'refunded_points' => $refundedPoints,
        ]);

        return redirect()->back()->banner(
            "Compra de \"{$product->name}\" reembolsada e removida ({$refundedPoints} SINFrões)."
        );
    }
}
