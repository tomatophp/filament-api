# Changelog

## v5.0.0

- Filament 5, Livewire 4, Laravel 12 and 13, PHP 8.2+.
- Relationship columns (e.g. `author.name`) are returned and searchable in the list endpoint (#5).
- Endpoints are served by a controller and build the resource form and table per request, so routes can be cached
  and a search no longer leaks into later requests.
- Queries use the resource `getEloquentQuery()`; record endpoints answer 404 for records outside it.
- Validation covers fields nested in sections, tabs and grids; only form fields are saved; 422 errors are JSON.
- Model policies are checked for the API user when they define the ability.
- New page hooks `getFilamentAPIForm()` and `getFilamentAPITable()`; new `filament-api:install` command.
- The endpoints of every panel are discovered, not only the default panel.
- Dropped the unused `tomatophp/console-helpers` dependency.
