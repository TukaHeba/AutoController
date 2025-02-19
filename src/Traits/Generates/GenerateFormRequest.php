<?php

namespace CodingPartners\AutoController\Traits\Generates;

use Illuminate\Support\Str;
use CodingPartners\AutoController\Helpers\ColumnFilter;
use CodingPartners\AutoController\Helpers\DirectoryMaker;

trait GenerateFormRequest
{
    /**
     * Generates the Store FormRequest file for the given model.
     *
     * @param string $model The model name.
     * @param array $columns The list of columns to include in validation.
     * @return void
     */
    protected function generateStoreFormRequest($model, array $columns)
    {
        $this->generateFormRequest($model, $columns, 'Store');
    }

    /**
     * Generates the Update FormRequest file for the given model.
     *
     * @param string $model The model name.
     * @param array $columns The list of columns to include in validation.
     * @return void
     */
    protected function generateUpdateFormRequest($model, array $columns)
    {
        $this->generateFormRequest($model, $columns, 'Update');
    }

    /**
     * Generates a FormRequest class for the given model and request type.
     *
     * This method creates a FormRequest file with validation rules based on the model's columns.
     * It ensures the necessary directory exists and prevents duplicate file creation.
     *
     * @param string $model The name of the model.
     * @param array $columns The list of columns to include in validation rules.
     * @param string $type The request type (either 'Store' or 'Update').
     * @return void
     */
    private function generateFormRequest($model, array $columns, string $type)
    {
        // Get the needed columns from the provided model
        $columns = ColumnFilter::getFilteredColumns($model, $columns, $type);

        // Define request file path
        $folderName = "{$model}";
        $requestName = $type . $model . 'Request';
        $requestPath = app_path("Http/Requests/{$folderName}/{$requestName}.php");

        // Ensure the directory exists
        DirectoryMaker::createDirectory(app_path("Http/Requests/{$folderName}"));

        // Generate the FormRequest if it doesn't already exist
        if (!file_exists($requestPath)) {

            $this->info("Generating {$type} FormRequest for $model...");

            // Generate request content
            $requestContent = $this->generateRequestContent($folderName, $requestName, $columns, $type);

            // Write content to file
            file_put_contents($requestPath, $requestContent);

            $this->info("$requestName created successfully.");
        }
    }

    /**
     * Generates validation rules for the given columns.
     *
     * @param array $columns The list of columns to generate rules for.
     * @param string $type The request type (either 'Store' or 'Update').
     * @return string The formatted validation rules.
     */
    private function generateValidationRules(array $columns, $type)
    {
        $rules = "";
        $defaultRule = $type === 'Store' ? 'required' : 'nullable';

        foreach ($columns as $column) {
            if (Str::endsWith($column, '_img')) {
                $rules .= "\n            '{$column}' => '{$defaultRule}|file|image|mimes:png,jpg,jpeg,gif|max:10000|mimetypes:image/jpeg,image/png,image/jpg,image/gif',";
            } elseif (Str::endsWith($column, '_vid')) {
                $rules .= "\n            '{$column}' => '{$defaultRule}|file|mimes:mp4, webm, ogg, mov, wmv|max:10000|mimetypes:video/mp4, video/webm, video/ogg, video/quicktime, video/x-ms-wmv',";
            } elseif (Str::endsWith($column, '_aud')) {
                $rules .= "\n            '{$column}' => '{$defaultRule}|file|mimes:mp3, wav, ogg, aac|max:10000|mimetypes:audio/mpeg, audio/wav, audio/ogg, audio/aac',";
            } elseif (Str::endsWith($column, '_docs')) {
                $rules .= "\n            '{$column}' => '{$defaultRule}|file|mimes:pdf, doc, docx, xls, xlsx, ppt, pptx|max:10000|mimetypes:application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation',";
            } else {
                $rules .= "\n            '{$column}' => '{$defaultRule}',";
            }
        }

        return $rules;
    }

    /**
     * Generates an array of human-readable attribute names for validation errors.
     *
     * This method processes an array of column names, removing media-related suffixes
     * (such as _img, _vid, _aud, _doc) and converting them into a more readable format.
     *
     * @param array $columns The list of column names to process.
     * @return string A formatted string representing the attributes array.
     */
    private function generateAttributes(array $columns): string
    {
        $attributes = "";
        $mediaSuffixes = ['_img', '_vid', '_aud', '_doc'];

        foreach ($columns as $column) {
            // Use the original column name as the key
            $key = $column;

            // Remove media suffix for display names, when present
            $value = Str::endsWith($column, $mediaSuffixes)
                ? Str::beforeLast($column, '_')
                : $column;

            // Convert the value as a human-readable label
            $attributes .= "\n            '$key' => '" . Str::headline($value) . "',";
        }

        return $attributes;
    }

    /**
     * Generates custom validation messages for the request.
     *
     * This method returns an array of custom error messages for validation failures.  
     * For 'StoreRequest', it provides a default message for required fields.  
     * For 'UpdateRequest', it returns a placeholder indicating that additional custom messages can be defined later.
     *
     * @param string $type The request type (either 'Store' or 'Update').
     * @return string The formatted validation messages as a string.
     */
    private function generateMessages($type)
    {
        return $type === 'Store' ? "'required' => 'The :attribute field is required.'" : "//";
    }

    /**
     * Generates the content of a FormRequest class.
     *
     * This method dynamically generates a Laravel FormRequest class with basic validation rules, 
     * attributes, and basic custom messages based on the provided columns and request type.
     *
     * @param string $folderName The folder name where the FormRequest class will be stored.
     * @param string $requestName The name of the FormRequest class.
     * @param array  $columns The list of columns for validation rules and attributes.
     * @param string $type The request type (either 'Store' or 'Update').
     * 
     * @return string The generated PHP content for the FormRequest class.
     */
    private function generateRequestContent($folderName, $requestName, $columns, $type)
    {
        return "<?php

namespace App\Http\Requests\\{$folderName};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use CodingPartners\AutoController\Traits\ApiResponseTrait;

class {$requestName} extends FormRequest
{
    use ApiResponseTrait;

    // Stop validation in the first failure
    protected \$stopOnFirstFailure = false;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * This method is called before validation starts to clean or normalize inputs.
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        \$this->merge([
            //
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [{$this->generateValidationRules($columns,$type)}
        ];
    }

    /**
     * Define human-readable attribute names for validation errors.
     * 
     * @return array<string, string>
     */
    public function attributes()
    {
        return [{$this->generateAttributes($columns)}
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            {$this->generateMessages($type)}
        ];
    }

    /**
     * Handle failed validation and return a JSON response with errors.
     * 
     * @param \Illuminate\Contracts\Validation\Validator \$Validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    protected function failedValidation(Validator \$Validator)
    {
        \$errors = \$Validator->errors()->all();
        throw new HttpResponseException(\$this->errorResponse(\$errors, 'Validation error', 422));
    }
}\n";
    }
}
