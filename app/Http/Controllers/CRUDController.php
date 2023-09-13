<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

abstract class CRUDController extends Controller
{
    protected string $model;

    protected string $view;

    protected array $rules = [];

    protected array $storeRules;

    protected array $updateRules;

    protected array $include = [];

    public function index()
    {
        $items = $this->model::paginate(10);

        return Inertia::render("CRUD/$this->view/Index", [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return Inertia::render("CRUD/$this->view/Create");
    }

    public function edit($id)
    {
        $item = $this->model::find($id);

        return Inertia::render("CRUD/$this->view/Edit", [
            'item' => $item,
        ]);
    }

    protected function created(array $new): ?array
    {
        return $new;
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules ?? $this->rules);

        $newValues = $this->created($validated);

        if ($newValues !== null) {
            $this->model::create($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    protected function updated(array $old, array $new): ?array
    {
        return $new;
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->updateRules ?? $this->rules);

        $model = $this->model::find($id);

        $newValues = $this->updated($model->toArray(), $validated);

        if ($newValues !== null) {
            $model->update($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    public function destroy($id)
    {
        $this->model::find($id)->delete();

        return redirect()->back();
    }
}
