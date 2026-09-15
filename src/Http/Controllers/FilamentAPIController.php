<?php

namespace TomatoPHP\FilamentApi\Http\Controllers;

use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentApi\Helpers\APIResponse;

/**
 * Serves the endpoints generated from resource pages. The resource form and table are built per request,
 * so the routes hold nothing but class names and can be cached with `php artisan route:cache`.
 */
class FilamentAPIController extends Controller
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $databaseColumns = [];

    public function index(Request $request): JsonResponse
    {
        $page = $this->page($request);
        $resource = $page::getResource();

        if ($denied = $this->deny($request, 'viewAny', $resource::getModel())) {
            return $denied;
        }

        $table = $this->table($page);
        $query = $resource::getEloquentQuery();
        $model = $query->getModel();
        $columns = $this->databaseColumns($model);

        $select = [$model->getKeyName()];
        $relations = [];
        $searchable = [];

        foreach ($table->getColumns() as $column) {
            if ($column->isHidden()) {
                continue;
            }

            $name = $column->getName();

            if ($column->isSearchable()) {
                $searchable[] = $name;
            }

            if ($this->isRelationColumn($model, $name)) {
                $relations[Str::beforeLast($name, '.')][] = Str::afterLast($name, '.');

                continue;
            }

            // A dotted name that is not a relationship reads a JSON column.
            if (in_array(Str::before($name, '.'), $columns, true)) {
                $select[] = Str::before($name, '.');
            }
        }

        $search = $request->query('search');

        if (is_string($search) && filled($search) && $searchable !== []) {
            $query->where(function (Builder $query) use ($searchable, $search, $model, $columns): void {
                foreach ($searchable as $name) {
                    if ($this->isRelationColumn($model, $name)) {
                        $query->orWhereRelation(Str::beforeLast($name, '.'), Str::afterLast($name, '.'), 'like', "%{$search}%");

                        continue;
                    }

                    if (in_array(Str::before($name, '.'), $columns, true)) {
                        $query->orWhere($model->qualifyColumn(str_replace('.', '->', $name)), 'like', "%{$search}%");
                    }
                }
            });
        }

        $eagerLoads = [];

        foreach ($relations as $path => $attributes) {
            $relationship = $model->{Str::before($path, '.')}();
            $select = [...$select, ...$this->parentKeys($relationship)];
            $relatedColumns = str_contains($path, '.') ? null : $this->relatedColumns($relationship, $attributes);

            $eagerLoads[$path] = fn (Relation $query) => $relatedColumns ? $query->select($relatedColumns) : $query;
        }

        $sort = $table->getDefaultSortColumn();

        if (filled($sort) && in_array($sort, $columns, true)) {
            $query->orderBy($model->qualifyColumn($sort), $table->getDefaultSortDirection() ?? 'asc');
        }

        $perPage = $table->getDefaultPaginationPageOption();

        $records = $query
            ->with($eagerLoads)
            ->select(array_map(fn (string $column): string => $model->qualifyColumn($column), array_values(array_unique($select))))
            ->paginate(is_numeric($perPage) ? (int) $perPage : 10)
            ->withQueryString();

        return $this->respond($request, $records);
    }

    public function show(Request $request, string $record): JsonResponse
    {
        $page = $this->page($request);
        $model = $page::getResource()::resolveRecordRouteBinding($record);

        if (! $model) {
            return $this->notFound();
        }

        if ($denied = $this->deny($request, 'view', $model)) {
            return $denied;
        }

        return $this->respond($request, $model);
    }

    public function store(Request $request): JsonResponse
    {
        $page = $this->page($request);
        $modelClass = $page::getResource()::getModel();

        if ($denied = $this->deny($request, 'create', $modelClass)) {
            return $denied;
        }

        $schema = $this->form($page, Schema::make(app($page))->model($modelClass)->operation('create'));
        $data = $this->validateForm($request, $schema);

        if ($data instanceof JsonResponse) {
            return $data;
        }

        return $this->respond($request, $modelClass::create($data));
    }

    public function update(Request $request, string $record): JsonResponse
    {
        $page = $this->page($request);
        $model = $page::getResource()::resolveRecordRouteBinding($record);

        if (! $model) {
            return $this->notFound();
        }

        if ($denied = $this->deny($request, 'update', $model)) {
            return $denied;
        }

        $schema = $this->form($page, Schema::make(app($page))->record($model)->operation('edit'));
        $data = $this->validateForm($request, $schema, $model);

        if ($data instanceof JsonResponse) {
            return $data;
        }

        $model->update($data);

        return $this->respond($request, $model->refresh());
    }

    public function destroy(Request $request, string $record): JsonResponse
    {
        $page = $this->page($request);
        $model = $page::getResource()::resolveRecordRouteBinding($record);

        if (! $model) {
            return $this->notFound();
        }

        if ($denied = $this->deny($request, 'delete', $model)) {
            return $denied;
        }

        $model->delete();

        return APIResponse::success();
    }

    /**
     * @return class-string<Page>
     */
    protected function page(Request $request): string
    {
        return $request->route()->getAction('filament_api_page');
    }

    /**
     * @param  class-string<Page>  $page
     */
    protected function table(string $page): Table
    {
        $table = Table::make(app($page));

        return method_exists($page, 'getFilamentAPITable')
            ? $page::getFilamentAPITable($table)
            : $page::getResource()::table($table);
    }

    /**
     * @param  class-string<Page>  $page
     */
    protected function form(string $page, Schema $schema): Schema
    {
        return method_exists($page, 'getFilamentAPIForm')
            ? $page::getFilamentAPIForm($schema)
            : $page::getResource()::form($schema);
    }

    /**
     * Validate the request with the rules of every field of the form, including fields inside sections,
     * tabs and grids, and keep only the values of fields that are saved.
     *
     * @return array<string, mixed>|JsonResponse
     */
    protected function validateForm(Request $request, Schema $schema, ?Model $record = null): array | JsonResponse
    {
        $rules = [];
        $attributes = [];
        $fillable = [];

        foreach ($schema->getFlatFields() as $field) {
            $statePath = $field->getStatePath();
            $fieldRules = $field->getValidationRules();

            if ($record) {
                foreach ($fieldRules as $index => $rule) {
                    if ($rule instanceof Unique) {
                        $fieldRules[$index] = $rule->ignore($record->getKey(), $record->getKeyName());
                    }
                }
            }

            $rules[$statePath] = $fieldRules;
            $attributes[$statePath] = $field->getValidationAttribute();

            if ($field->isDehydrated()) {
                $fillable[] = $statePath;
            }
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        return $request->only($fillable);
    }

    /**
     * Apply the resource policy when it defines the ability, like Filament does for the panel.
     */
    protected function deny(Request $request, string $ability, Model | string $target): ?JsonResponse
    {
        $policy = Gate::getPolicyFor($target);

        if (! $policy || ! method_exists($policy, $ability)) {
            return null;
        }

        if (Gate::forUser($request->user())->allows($ability, $target)) {
            return null;
        }

        return APIResponse::error(__('This action is unauthorized.'), 403);
    }

    protected function respond(Request $request, mixed $data): JsonResponse
    {
        $resource = $request->route()->getAction('filament_api_resource');

        if ($resource) {
            $data = $data instanceof LengthAwarePaginator ? $resource::collection($data) : $resource::make($data);
        }

        return APIResponse::success($data);
    }

    protected function notFound(): JsonResponse
    {
        return APIResponse::error(__('Not Found'), 404);
    }

    protected function isRelationColumn(Model $model, string $name): bool
    {
        return str_contains($name, '.') && $model->isRelation(Str::before($name, '.'));
    }

    /**
     * The columns of the parent row a relationship needs to be eager loaded.
     *
     * @return array<int, string>
     */
    protected function parentKeys(Relation $relationship): array
    {
        return match (true) {
            $relationship instanceof MorphTo => [$relationship->getForeignKeyName(), $relationship->getMorphType()],
            $relationship instanceof BelongsTo => [$relationship->getForeignKeyName()],
            $relationship instanceof HasOneOrMany, $relationship instanceof HasOneOrManyThrough => [$relationship->getLocalKeyName()],
            $relationship instanceof BelongsToMany => [$relationship->getParentKeyName()],
            default => [],
        };
    }

    /**
     * The related columns to select, or null to load whole related rows
     * (accessors, pivots, polymorphic and through relationships).
     *
     * @param  array<int, string>  $attributes
     * @return array<int, string>|null
     */
    protected function relatedColumns(Relation $relationship, array $attributes): ?array
    {
        $key = match (true) {
            $relationship instanceof MorphTo, $relationship instanceof MorphOneOrMany => null,
            $relationship instanceof BelongsTo => $relationship->getOwnerKeyName(),
            $relationship instanceof HasOneOrMany => $relationship->getForeignKeyName(),
            default => null,
        };

        if ($key === null) {
            return null;
        }

        $related = $relationship->getRelated();

        if (array_diff($attributes, $this->databaseColumns($related)) !== []) {
            return null;
        }

        return array_map(
            fn (string $column): string => $related->qualifyColumn($column),
            array_values(array_unique([$related->getKeyName(), $key, ...$attributes])),
        );
    }

    /**
     * @return array<int, string>
     */
    protected function databaseColumns(Model $model): array
    {
        $key = $model->getConnectionName() . '.' . $model->getTable();

        return $this->databaseColumns[$key] ??= $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
    }
}
