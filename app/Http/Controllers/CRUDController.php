<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Edition;
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

    /** Relations needed by list rows. Defaults to the edit relations. */
    protected function indexLoad(): array
    {
        return $this->load;
    }

    /** Relations needed when editing an individual record. */
    protected function editLoad(): array
    {
        return $this->load;
    }

    /** Relation counts needed by list rows without loading every related model. */
    protected function indexWithCount(): array
    {
        return [];
    }

    /** Global relations that list rows do not need. */
    protected function indexWithout(): array
    {
        return [];
    }

    /** Relation path to edition_id for records without a direct edition_id column. */
    protected function editionRelationPath(): ?string
    {
        return null;
    }

    /** Related text fields searchable on pages without a Scout index. */
    protected function searchRelations(): array
    {
        return [];
    }

    /**
     * Common method to get paginated items, filters, search and relations.
     *
     * @return array<string, mixed>
     */
    protected function getIndexData(Request $request): array
    {
        $modelUsesScout = in_array(Searchable::class, class_uses($this->model));

        $search = $request->query('query');
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $modelInstance = new $this->model;
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        $columnMetadata = $schema->getColumns($table);
        $columns = array_column($columnMetadata, 'name');
        $searchableColumns = array_values(array_map(
            fn (array $column) => $column['name'],
            array_filter($columnMetadata, function (array $column) {
                $type = strtolower($column['type']);

                return str_contains($type, 'char') || str_contains($type, 'text');
            }),
        ));
        $isSearchable = $modelUsesScout || $searchableColumns !== [];
        $editionRelationPath = $this->editionRelationPath();
        $hasEditionFilter = in_array('edition_id', $columns, true) || $editionRelationPath !== null;
        $editions = $hasEditionFilter
            ? Edition::query()->select('id', 'name', 'year')->orderByDesc('year')->get()
            : collect();
        $activeEdition = $editions->first();
        $requestedEdition = $request->query('edition_id');
        $editionFilter = $requestedEdition === 'all'
            ? null
            : ($requestedEdition ?? $activeEdition?->id);

        $applySortAndFilters = function (Builder $query) use ($table, $columns, $sortBy, $sortDir, $request, $editionFilter, $editionRelationPath) {
            $reserved = ['page', 'query', 'sort_by', 'sort_dir', 'filter_by', 'filter_val', 'edition_id'];
            foreach ($request->query() as $key => $value) {
                if (! in_array($key, $reserved) && $value !== null && $value !== '') {
                    if (in_array($key, $columns, true)) {
                        $query->where($table.'.'.$key, $value);
                    }
                }
            }

            if ($request->filled('filter_by') && $request->has('filter_val') && $request->get('filter_val') !== '') {
                $filterCol = $request->query('filter_by');
                if (in_array($filterCol, $columns, true)) {
                    $query->where($table.'.'.$filterCol, $request->query('filter_val'));
                }
            }

            if ($editionFilter !== null) {
                if (in_array('edition_id', $columns, true)) {
                    $query->where($table.'.edition_id', $editionFilter);
                } elseif ($editionRelationPath !== null) {
                    $query->whereHas($editionRelationPath, fn (Builder $related) => $related->where('edition_id', $editionFilter));
                }
            }

            if (in_array($sortBy, $columns, true)) {
                $query->orderBy($table.'.'.$sortBy, $sortDir);
            } else {
                $query->orderBy($table.'.id', 'asc');
            }
        };

        if ($search !== null && trim($search) !== '' && $modelUsesScout) {
            $query = $this->model::search($search)->query(function (Builder $query) use ($applySortAndFilters) {
                $query->without($this->indexWithout())
                    ->with($this->indexLoad())
                    ->withCount($this->indexWithCount());
                $applySortAndFilters($query);
            });
        } else {
            $query = $this->model::without($this->indexWithout())
                ->with($this->indexLoad())
                ->withCount($this->indexWithCount());

            if ($search !== null && trim($search) !== '' && ($searchableColumns !== [] || $this->searchRelations() !== [])) {
                $term = trim($search);
                $operator = $modelInstance->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $searchRelations = $this->searchRelations();

                $query->where(function (Builder $query) use ($searchableColumns, $searchRelations, $table, $operator, $term) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($table.'.'.$column, $operator, '%'.$term.'%');
                    }

                    foreach ($searchRelations as $relation => $columns) {
                        $query->orWhereHas($relation, function (Builder $related) use ($columns, $operator, $term) {
                            $related->where(function (Builder $relatedSearch) use ($columns, $operator, $term) {
                                foreach ($columns as $column) {
                                    $relatedSearch->orWhere($relatedSearch->getModel()->qualifyColumn($column), $operator, '%'.$term.'%');
                                }
                            });
                        });
                    }
                });
            }

            $applySortAndFilters($query);
        }

        $items = $query->simplePaginate()->appends($request->query());

        return [
            'items' => $items,
            'with' => fn () => $this->with(),
            'editionOptions' => $hasEditionFilter
                ? fn () => $editions->pluck('name', 'id')->all()
                : [],
            'defaultFilters' => $activeEdition && $requestedEdition === null
                ? ['edition_id' => (string) $activeEdition->id]
                : [],
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
        $data['item'] = $item ? $item->load($this->editLoad()) : null;

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
