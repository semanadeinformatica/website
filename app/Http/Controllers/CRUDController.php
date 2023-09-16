<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

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
     * @param  T  $old The old model.
     * @return array<string, string | array<int|string>>
     */
    protected function storeRules(): array
    {
        return $this->rules;
    }

    /**
     * The validation rules for the update method.
     *
     * @param  T  $old The old model.
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

    public function index()
    {
        $items = $this->model::paginate(10);

        $with = $this->with();

        return Inertia::render("CRUD/$this->view/Index", [
            'items' => $items,
            'with' => $with,
        ]);
    }

    public function show($id)
    {
        $item = $this->model::find($id);

        $with = $this->with();

        return Inertia::render("CRUD/$this->view/Show", [
            'item' => $item,
            'with' => $with,
        ]);
    }

    public function create()
    {
        $with = $this->with();

        return Inertia::render("CRUD/$this->view/Create", [
            'with' => $with,
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::find($id);

        $with = $this->with();

        return Inertia::render("CRUD/$this->view/Edit", [
            'item' => $item,
            'with' => $with,
        ]);
    }

    /**
     * Gets called after the validation of the store method.
     * The returned array will be used to create the new model,
     * unless null is returned, in which case no model will be created.
     *
     * @param  array<string, mixed>  $new The validated values.
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
            $this->model::create($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    /**
     * Gets called after the validation of the update method.
     * The returned array will be used to update the model, unless
     * null is returned, in which case the model will not be updated.
     *
     * @param  T  $old The old model.
     * @param  array<string, mixed>  $new The validated values.
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
            $model->update($newValues);
        }

        return redirect()->action([static::class, 'index']);
    }

    /**
     * Gets called before the model is deleted.
     * If true is returned, the model will be deleted.
     * If false is returned, the model will not be deleted.
     *
     * @param  T  $old The old model.
     */
    protected function destroyed($old): bool
    {
        return true;
    }

    public function destroy($id)
    {
        $model = $this->model::find($id);

        if ($this->destroyed($model->toArray())) {
            $model->delete();
        }

        return redirect()->back();
    }
}
