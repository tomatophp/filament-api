<?php

namespace TomatoPHP\FilamentApi\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sushi\Sushi;

class APIResource extends Model
{
    use Sushi;

    protected $schema = [
        "method" => "string",
        "slug" => "string",
        "name" => "string",
        "table" => "string",
        "middleware" => "json",
    ];

    public function getRows()
    {
        $resources = Filament::getResources();
        $routes = [];
        foreach ($resources as $resource){
            $pages = app($resource)->getPages();
            foreach ($pages as $page){
                $page = app($page->getPage());
                if(get_class_methods($page) && in_array('TomatoPHP\FilamentApi\Traits\InteractWithAPI', class_uses($page))){
                    foreach ($page::registerAPIRoutes() as $item){
                        $routes[] = [
                            "method" => Str::of($item['method'])->upper()->toString(),
                            "slug" => config('filament-api.api_prefix').'/'.$item['slug'],
                            "name" => "filament.api.".$item['name'],
                            "table" => $item['table'],
                            "middleware" => json_encode($item['middleware']),
                        ];
                    }
                }
            }
        }

        return $routes;
    }
}
