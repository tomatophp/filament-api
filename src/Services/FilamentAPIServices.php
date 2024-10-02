<?php

namespace TomatoPHP\FilamentApi\Services;

use Closure;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentApi\Helpers\APIResponse;

class FilamentAPIServices
{
    private ?array $routes = [];

    public function register(
        Page $page,
        Closure $form,
        ?Closure $table = null,
        ?string $type = null,
        ?string $resource = null,
        ?array $middleware = null,
        ?string $slug = null
    ): array {
        return $this->generate(
            $page,
            $form(new Form($page)),
            $table ? $table(new Table($page)) : null,
            $type,
            $resource,
            $middleware,
            $slug
        );
    }

    public function generate(
        Page $page,
        Form $form,
        ?Table $table = null,
        ?string $type = null,
        ?string $resource = null,
        ?array $middleware = null,
        ?string $slug = null
    ): array {
        $routes = [];
        $query = app($page::getResource()::getModel());
        $slug  = $slug ?? $page::getResource()::getSlug();

        // Index Method
        if ($type === 'list' || $type === 'manager') {
            $routes[] = [
                "table" => $slug,
                "method" => "get",
                "slug" => $slug,
                "callback" => function (Request $request) use ($query, $table, $resource) {
                    return $this->index($request, $query, $table, $resource);
                },
                "name" => $slug . ".index",
                "middleware" => $middleware
            ];

            // Delete Method
            $routes[] = [
                "table" => $slug,
                "method" => "delete",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page) {
                    return $this->destroy($record, $request, $page);
                },
                "name" => $slug . ".destroy",
                "middleware" => $middleware
            ];
        }

        // Store Method
        if ($type === 'create'  || $type === 'manager') {
            $routes[] = [
                "table" => $slug,
                "method" => "post",
                "slug" => $slug,
                "callback" => function (Request $request) use ($page, $form, $resource) {
                    return $this->store($request, $page, $form, $resource);
                },
                "name" => $slug . ".store",
                "middleware" => $middleware
            ];
        }

        // Update Method
        if ($type === 'edit'  || $type === 'manager') {
            $routes[] = [
                "table" => $slug,
                "method" => "put",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page, $form, $resource) {
                    return $this->update($record, $request, $page, $form, $resource);
                },
                "name" => $slug . ".update",
                "middleware" => $middleware
            ];
        }

        // View Method
        if ($type === 'view'  || $type === 'manager') {
            $routes[] = [
                "table" => $slug,
                "method" => "get",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page, $resource) {
                    return $this->show($record, $request, $page, $resource);
                },
                "name" => $slug . ".show",
                "middleware" => $middleware
            ];
        }



        return $routes;
    }

    public function routes(array $routes): void
    {
        $this->routes = array_merge($this->routes, $routes);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    protected function index(Request $request, Model $query, Table $table, ?string $resource = null)
    {
        $primaryKey = $query->getKeyName();

        if ($request->has('search') && !empty($request->get('search'))) {
            $searchableColumns = [];
            foreach ($table->getColumns() as $column) {

                if ($column->isSearchable()) {
                    $searchableColumns[] = $column->getName();
                }
            }

            $query = $this->addWheres($query, $searchableColumns, $request);
        }

        $visibleColumns = [];
        [$visibleColumns, $relatedColumns] = $this->getParsedColumns($table->getColumns());
        $visibleColumns[] = $primaryKey;

        [$query, $visibleColumns] = $this->addRelations($query, $visibleColumns, $relatedColumns);

        $query = $query->select(array_unique($visibleColumns));
        if ($table->getDefaultSortColumn() && $table->getDefaultSortDirection()) {
            $query = $query->orderBy($table->getDefaultSortColumn(), $table->getDefaultSortDirection());
        }

        if ($resource) {
            return APIResponse::success($resource::collection($query->paginate($table->getDefaultPaginationPageOption())));
        }

        return APIResponse::success($query->paginate($table->getDefaultPaginationPageOption()));
    }

    protected function addRelations($query, $visibleColumns, $relatedColumns)
    {
        foreach ($relatedColumns as $key => $columns) {

            $query = $query->with([$key => function ($query) use ($key, $columns) {
                if (str_contains(strtolower(get_class($query)), 'has')) {
                    $columns[] = $query->getForeignKeyName();
                } else {
                    $columns[] = $query->getOwnerKeyName();
                }
                $query->select(array_unique($columns));
            }]);


            if (str_contains(strtolower(get_class($query->getRelation($key))), 'has')) {
                $visibleColumns[] = $query->getRelation($key)->getLocalKeyName();
            } else {
                $visibleColumns[] = $query->getRelation($key)->getForeignKeyName();
            }
        }

        return [$query, $visibleColumns];
    }
    protected function addWheres($query, $searchableColumns, $request)
    {
        return $query::where(function ($query) use ($searchableColumns, $request) {
            foreach ($searchableColumns as $column) {
                if (str_contains($column, '.')) {
                    $columnSplit = explode('.', $column);
                    $query->orWhereHas($columnSplit[0], function ($query) use ($columnSplit, $request) {
                        $query->where($columnSplit[1], 'like', '%' . $request->get('search') . '%');
                    });
                    continue;
                }
                $query->orWhere($column, 'like', '%' . $request->get('search') . '%');
            }
        });
    }
    protected function getParsedColumns($columns)
    {
        $visibleColumns = [];
        $relatedColumns = [];
        foreach ($columns as $column) {
            if ($column->isVisible()) {
                if (str_contains($column->getName(), '.')) {
                    $columnSplit = explode('.', $column->getName());
                    $relatedColumns[$columnSplit[0]][] = $columnSplit[1];
                    continue;
                }
                $visibleColumns[] = $column->getName();
            }
        }

        return [$visibleColumns, $relatedColumns];
    }

    protected function show(int $record, Request $request, Page $page, ?string $resource = null)
    {
        $record = app($page::getResource())->getModel()::find($record);

        if ($resource) {
            return APIResponse::success($resource::make($record));
        }

        return APIResponse::success($record);
    }

    protected function store(Request $request, $page, $form, ?string $resource = null)
    {
        $rules = [];
        $components = $form->getComponents();
        foreach ($components as $component) {
            $rules[$component->getId()] = array_values($component->getValidationRules());
        }

        $request->validate($rules);

        $record = app($page::getResource())->getModel()::create($request->all());

        if ($resource) {
            return APIResponse::success($resource::make($record));
        }

        return APIResponse::success($record);
    }

    protected function update($record, Request $request, $page, $form, ?string $resource = null)
    {
        $record = app($page::getResource())->getModel()::find($record);
        $rules = [];
        $components = $form->getComponents();
        foreach ($components as $component) {
            $validation = $component->getValidationRules();
            foreach ($validation as $key => $value) {
                if ($value instanceof Unique) {
                    $validation[$key] = $value->ignore($record->id);
                }
            }
            $rules[$component->getId()] = $validation;
        }

        $request->validate($rules);

        $record->update($request->all());

        if ($resource) {
            return APIResponse::success($resource::make($record));
        }

        return APIResponse::success($record);
    }

    protected function destroy($record, Request $request, $page)
    {
        $record = app($page::getResource())->getModel()::find($record)?->delete();

        return APIResponse::success();
    }
}
