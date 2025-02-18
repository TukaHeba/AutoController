<?php

namespace CodingPartners\AutoController\Traits\Generates;

use Illuminate\Support\Str;
use CodingPartners\AutoController\Helpers\ColumnFilter;
use CodingPartners\AutoController\Helpers\DirectoryMaker;

trait GenerateResource
{
    /**
     * Generates a Resource class for the given model.
     *
     * This method creates a new Resource class file for the specified model.
     * It first filters out unwanted columns from the given array of columns.
     * Then, it constructs the file path for the Resource class and checks if the directory exists.
     * If not, it creates the directory.
     * Next, it checks if the Resource class file already exists. If not, it creates the file with the necessary content.
     * The content includes the class definition, the toArray method, and assignments for each column.
     *
     * @param string $model The name of the model for which the Resource class is being generated.
     * @param array $columns An array of column names for the specified model.
     *
     * @return void
     */
    protected function generateResource($model, array $columns)
    {
        // Get the needed columns from the provided model
        $columns = ColumnFilter::getFilteredColumns($model, $columns, 'resource');

        $resourceName = $model . 'Resource';
        $resourcePath = app_path("Http/Resources/{$resourceName}.php");

        // Check if the App\Http\Resources directory exists, if not, create it
        DirectoryMaker::createDirectory(app_path("Http/Resources"));

        // Check if the Resource class file exists, if not, create it
        if (!file_exists($resourcePath)) {

            $this->info("Generating Resource for $model...");

            $assignments = "";
            $mediaSuffixes = ['_img', '_vid', '_aud', '_doc'];

            foreach ($columns as $column) {
                if (Str::endsWith($column, $mediaSuffixes)) {
                    $assignments .= "\n            '{$column}' => asset(\$this->$column),";
                } else {
                    $assignments .= "\n            '{$column}' => \$this->$column,";
                }
            }

            $resourceContent = "<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class {$resourceName} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  \$request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(\$request)
    {
        return [{$assignments}
        ];
    }
}\n";
            file_put_contents($resourcePath, $resourceContent);
            $this->info("Resource $resourceName created successfully.");
        }
    }
}
