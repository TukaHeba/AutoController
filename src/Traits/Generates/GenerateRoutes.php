<?php

namespace CodingPartners\AutoController\Traits\Generates;

use Illuminate\Support\Str;

trait GenerateRoutes
{
    /**
     * Generate and add API routes for a given model, avoiding duplicates.
     *
     * This method generates RESTful API routes for a specific model and adds them to the `routes/api.php` file.
     * The generated routes allow for managing the specified model through standard RESTful operations.
     * If `$softDeleteRoutes` is set to true, additional routes for handling soft deleted records
     * (trashed, restore, and force delete) will also be generated.
     * Before adding the routes, the method checks if the routes already exist to avoid duplicates.
     * 
     * @param string $model The name of the model for which the routes are being generated.
     * @param bool $softDeleteRoutes Whether to include routes for handling soft deleted records.
     *
     * @return void
     */
    protected function generateRoutes($model, $softDeleteRoutes)
    {
        $this->info("Generating routes for $model...");

        $models = Str::plural(Str::snake($model, '-'));
        $filePath = base_path('routes/api.php');

        // Read existing routes in api.php file.
        $existingRoutes = file_get_contents($filePath);

        // Define route patterns
        $routes = [
            "main" => "Route::apiResource('{$models}', App\Http\Controllers\\{$model}Controller::class);",
            "trashed" => "Route::get('{$models}/trashed', 'trashed');",
            "restore" => "Route::post('{$models}/{id}/restore', 'restore');",
            "forceDelete" => "Route::delete('{$models}/{id}/forceDelete', 'forceDelete');",
        ];

        $routesContent = "\n\n/**
* {$model} Management Routes
*
* These routes handle {$model} management operations.
*/
Route::controller(App\Http\Controllers\\{$model}Controller::class)->group(function () {";

        // Flag to check if new routes were added
        $routesAdded = false;

        // Check and add soft delete routes individually
        if ($softDeleteRoutes) {
            foreach (["trashed", "restore", "forceDelete"] as $route) {
                if (!str_contains($existingRoutes, $routes[$route])) {
                    $routesContent .= "\n\t" . $routes[$route];
                    $routesAdded = true;
                }
            }
        }

        // Check and add main resource route
        if (!str_contains($existingRoutes, $routes["main"])) {
            $routesContent .= "\n\t" . $routes["main"];
            $routesAdded = true;
        }

        $routesContent .= "\n});";

        // Append only if new routes were added
        if ($routesAdded) {
            file_put_contents($filePath, $routesContent, FILE_APPEND);
            $this->info("$model routes added successfully.");
        } else {
            $this->info("All $model routes already exist. Skipping...");
        }
    }
}
