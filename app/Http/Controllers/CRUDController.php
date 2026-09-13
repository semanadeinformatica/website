<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Scout\Searchable;
use Nette\Utils\Json;

/**
 * @template T
 */
abstract class CRUDController extends Controller
{
    /**
     * The model class name.
     *
     * @var class-string<T>
     */
    protected string $model;

    /**
     * The Inertia view subfolder in the CRUD folder.
     */
    protected string $view;

    /**
     * The validation rules.
     *
     * @var array<string, string | array<int|string>>
     */
    protected array $rules = [];

    /**
     * The validation rules for the store method.
     *
     * @param  T  $old  The old model.
     * @return array<string, string | array<int|string>>
     */
    protected function storeRules(): array
    {
        return $this->rules;
    }

    /**
     * The validation rules for the update method.
     *
     * @param  T  $old  The old model.
     * @return array<string, string | array<int|string>>
     */
    protected function updateRules($old): array
    {
        return $this->rules;
    }

    /**
     * The array to include with the views.
     *
     * @return array<mixed, mixed>
     */
    protected function with(): array
    {
        return [];
    }

    /**
     * The associated relations to load on the model when rendering items.
     */
    protected $load = [];

    /**
     * Common method to get paginated items, filters, search and relations.
     *
     * @return array<string, mixed>
     */
    protected function getIndexData(Request $request): array
    {
        $isSearchable = in_array(Searchable::class, class_uses($this->model));

        $search = $request->query('query');
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $modelInstance = new $this->model;
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();

        $applySortAndFilters = function (Builder $query) use ($table, $schema, $sortBy, $sortDir, $request) {
            $reserved = ['page', 'query', 'sort_by', 'sort_dir', 'filter_by', 'filter_val'];
            foreach ($request->query() as $key => $value) {
                if (!in_array($key, $reserved) && $value !== null && $value !== '') {
                    if ($schema->hasColumn($table, $key)) {
                        $query->where($table . '.' . $key, $value);
                    }
                }
            }

            if ($request->filled('filter_by') && $request->has('filter_val') && $request->get('filter_val') !== '') {
                $filterCol = $request->query('filter_by');
                if ($schema->hasColumn($table, $filterCol)) {
                    $query->where($table . '.' . $filterCol, $request->query('filter_val'));
                }
            }

            if ($schema->hasColumn($table, $sortBy)) {
                $query->orderBy($table . '.' . $sortBy, $sortDir);
            } else {
                $query->orderBy($table . '.id', 'asc');
            }
        };

        if ($isSearchable && $search !== null && trim($search) !== '') {
            $query = $this->model::search($search)->query(function (Builder $query) use ($applySortAndFilters) {
                $query->with($this->load);
                $applySortAndFilters($query);
            });
        } else {
            $query = $this->model::with($this->load);
            $applySortAndFilters($query);
        }

        $items = $query->paginate()->appends($request->query());

        return [
            'items' => $items,
            'with' => $this->with(),
            'isSearchable' => $isSearchable,
            'view' => $this->view,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getIndexData($request);

        Log::info('Displaying all {model} records with query: {search}', ['model' => $this->model, 'search' => $request->query('query') ?? 'none']);

        return Inertia::render("CRUD/{$this->view}/Index", $data);
    }

    public function show(Request $request, $id)
    {
        return $this->edit($request, $id);
    }

    public function create(Request $request)
    {
        $data = $this->getIndexData($request);
        $data['modal'] = 'create';

        return Inertia::render("CRUD/{$this->view}/Index", $data);
    }

    public function edit(Request $request, $id)
    {
        $data = $this->getIndexData($request);
        $item = $this->model::find($id);
        $data['modal'] = 'edit';
        $data['item'] = $item ? $item->load($this->load) : null;

        return Inertia::render("CRUD/{$this->view}/Index", $data);
    }

    /**
     * Gets called after the validation of the store method.
     * The returned array will be used to create the new model,
     * unless null is returned, in which case no model will be created.
     *
     * @param  array<string, mixed>  $new  The validated values.
     * @return array<string, mixed>|null
     */
    protected function created(array $new): ?array
    {
        return $new;
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules());

        $newValues = $this->created($validated);

        if ($newValues !== null) {
            Log::info('Creating new {model} record with values: {values}', ['model' => $this->model, 'values' => Json::encode($newValues, true)]);

            $this->model::create($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    /**
     * Gets called after the validation of the update method.
     * The returned array will be used to update the model, unless
     * null is returned, in which case the model will not be updated.
     *
     * @param  T  $old  The old model.
     * @param  array<string, mixed>  $new  The validated values.
     * @return array<string, mixed>|null
     */
    protected function updated($old, array $new): ?array
    {
        return $new;
    }

    public function update(Request $request, $id)
    {
        $model = $this->model::find($id);

        $validated = $request->validate($this->updateRules($model));

        $newValues = $this->updated($model, $validated);

        if ($newValues !== null) {
            Log::info('Updating {model} record with id {id} with values: {values}', ['model' => $this->model, 'id' => $model->id, 'values' => Json::encode($newValues, true)]);

            $model->update($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    /**
     * Gets called before the model is deleted.
     * If true is returned, the model will be deleted.
     * If false is returned, the model will not be deleted.
     *
     * @param  T  $old  The old model.
     */
    protected function destroyed($old): bool
    {
        return true;
    }

    public function destroy($id)
    {
        $model = $this->model::find($id);

        if ($this->destroyed($model->toArray())) {
            Log::alert('Deleting {model} record with id {id}', ['model' => $this->model, 'id' => $model->id]);

            $model->delete();
        }

        return redirect()->back();
    }
}
