<?php

namespace TomatoPHP\FilamentApi\Services;

use Closure;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentApi\Helpers\APIResponse;

class FilamentAPIServices
{
    private ?Page $page=null;
    private ?Table $table=null;
    private ?Form $form=null;
    private ?array $routes=[];

    public function register(
        Page $page,
        Closure $form,
        ?Closure $table=null,
        ?string $type=null,
        ?string $resource=null,
        ?array $middleware=null,
        ?string $slug=null
    ): array
    {
        return $this->generate(
            $page,
            $form(new Form($page)),
            $table? $table(new Table($page)) : null,
            $type,
            $resource,
            $middleware,
            $slug
        );
    }

    public function generate(
        Page $page,
        Form $form,
        ?Table $table=null,
        ?string $type=null,
        ?string $resource=null,
        ?array $middleware=null,
        ?string $slug=null
    ): array
    {
        $routes = [];
        $query = $page::getResource()::getEloquentQuery();
        $slug  = $slug ?? $page::getResource()::getSlug();

        // Index Method
        if($type === 'list' || $type === 'manager'){
            $routes[] = [
              "table"=> $slug,
              "method" => "get",
              "slug" => $slug,
              "callback" => function(Request $request) use ($query, $page, $table, $resource){
                  return $this->index($request, $query, $page, $table, $resource);
              },
              "name" => $slug . ".index",
              "middleware" => $middleware
            ];

            // Delete Method
            $routes[] = [
                "table"=> $slug,
                "method" => "delete",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page){
                    return $this->destroy($record, $request, $page);
                },
                "name" => $slug . ".destroy",
                "middleware" => $middleware
            ];
        }

        // Store Method
        if($type === 'create'  || $type === 'manager'){
            $routes[] = [
                "table"=> $slug,
                "method" => "post",
                "slug" => $slug,
                "callback" => function (Request $request) use ($page, $form, $resource){
                    return $this->store($request, $page, $form, $resource);
                },
                "name" => $slug . ".store",
                "middleware" => $middleware
            ];
        }

        // Update Method
        if($type === 'edit'  || $type === 'manager'){
            $routes[] = [
                "table"=> $slug,
                "method" => "put",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page, $form, $resource){
                    return $this->update($record, $request, $page, $form, $resource);
                },
                "name" => $slug . ".update",
                "middleware" => $middleware
            ];
        }

        // View Method
        if($type === 'view'  || $type === 'manager'){
            $routes[] = [
                "table"=> $slug,
                "method" => "get",
                "slug" => $slug. '/{model}',
                "callback" => function ($record, Request $request) use ($page, $resource){
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
        $this->routes = array_merge($this->routes , $routes);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    protected function index(Request $request,Builder $query,Page $page,Table $table,?string $resource=null)
    {
        if($request->has('search') && !empty($request->get('search'))){
            $searchableColumns = [];
            foreach ($table->getColumns() as $column){
                if($column->isSearchable()){
                    $searchableColumns[] = $column->getName();
                }
            }

            $query->where(function($query) use ($searchableColumns, $request){
                foreach ($searchableColumns as $column){
                    $query->orWhere($column, 'like', '%' . $request->get('search') . '%');
                }
            });
        }

        $visiableColumns = [];
        foreach ($table->getColumns() as $column){
            if($column->isVisible()){
                $visiableColumns[] = $column->getName();
            }
        }

        $query->select($visiableColumns);

        if($table->getDefaultSortColumn() && $table->getDefaultSortDirection()){
            $query->orderBy($table->getDefaultSortColumn(), $table->getDefaultSortDirection());
        }

        if($resource){
            return APIResponse::success($resource::collection($query->paginate($table->getDefaultPaginationPageOption())));
        }

        return APIResponse::success($query->paginate($table->getDefaultPaginationPageOption()));
    }

    protected function show(int $record, Request $request,Page $page,?string $resource=null)
    {
        $resourceClass = app($page::getResource());
        $indexPage = app($resourceClass->getPages()['index']->getPage());
        $table = $resourceClass->table(new Table($indexPage));
        $record = app($page::getResource())->getModel()::find($record);

        if($resource) {
            return APIResponse::success($resource::make($record));
        }

        return APIResponse::success($record);
    }

    protected function store(Request $request, $page, $form,?string $resource=null)
    {
        $rules = [];
        $components = $form->getComponents();
        foreach ($components as $component) {
            $rules[$component->getId()] = array_values($component->getValidationRules());
        }

        $request->validate($rules);

        $record = app($page::getResource())->getModel()::create($request->all());

        if($resource) {
            return APIResponse::success($resource::make($record));
        }

        return APIResponse::success($record);
    }

    protected function update($record, Request $request, $page, $form,?string $resource=null)
    {
        $record = app($page::getResource())->getModel()::find($record);
        $rules = [];
        $components = $form->getComponents();
        foreach ($components as $component) {
            $validation = $component->getValidationRules();
            foreach ($validation as $key => $value){
                if($value instanceof Unique){
                    $validation[$key] = $value->ignore($record->id);
                }
            }
            $rules[$component->getId()] = $validation;
        }

        $request->validate($rules);

        $record->update($request->all());

        if($resource) {
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
