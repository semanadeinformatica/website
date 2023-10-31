<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
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

    /**
     * The associated relations to load on the model when rendering the edit view.
     */
    protected $load = [];

    public function index(Request $request)
    {
        $isSearchable = in_array(\Laravel\Scout\Searchable::class, class_uses($this->model));

        $search = $request->query('query');
        if ($isSearchable && $search !== null) {
            $query = $this->model::search($search);
        } else {
            $query = $this->model::orderBy('id');
        }

        $filteredQuery = collect($request->query())
            ->intersectByKeys(['sort_by' => '', 'sort_dir' => '', 'query' => '', 'filter_by' => '']);
        $items = $query->paginate()->appends($filteredQuery->toArray());

        $with = $this->with();

        Log::info('Displaying all {model} records with query: {search}', ['model' => $this->model, 'search' => $search ?? 'none']);

        return Inertia::render("CRUD/{$this->view}/Index", [
            'items' => $items,
            'with' => $with,
            'isSearchable' => $isSearchable,
        ]);
    }

    public function show($id)
    {
        $item = $this->model::find($id);

        $with = $this->with();

        Log::info('Displaying {model} record with id: {id}', ['model' => $this->model, 'id' => $id]);

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
            'item' => $item->load($this->load),
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

            Log::alert('Deleting {model} record with id {id}', ['model' => $this->model, 'id' => $model->id]);

            $model->delete();
        }

        return redirect()->back();
    }
}
