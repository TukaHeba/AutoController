<?php

namespace CodingPartners\AutoController\Helpers;

class ColumnFilter
{
    /**
     * Filters columns based on the model and scope.
     *
     * This method provides functionality to filter columns based on the model and scope. 
     * It excludes certain columns by default, with custom exclusions for specific cases.
     * 
     * By default, excludes `id`, `created_at`, `updated_at`, and `deleted_at`.
     * For the `User` model, also excludes `email_verified_at` and `remember_token`.
     * In the 'resource' scope, `id` is kept, and `password` is excluded for `User` model.
     *
     * @param string $model The model name.
     * @param array $columns The list of columns to be filtered.
     * @param string $scope The filtering scope.
     * 
     * @return array The filtered list of columns after applying exclusions.
     */
    public static function getFilteredColumns(string $model, array $columns, string $scope): array
    {
        // Common exclusions for all models
        $exclusions = ['id', 'created_at', 'updated_at', 'deleted_at'];

        // Additional exclusions for the 'User' model
        if ($model === 'User') {
            $exclusions = array_merge($exclusions, ['email_verified_at', 'remember_token']);
        }

        // Modify exclusions for the 'resource' scope. 
        if ($scope === 'resource') {
            // Remove 'id' column from the exclusions array
            $exclusions = array_filter($exclusions, fn($col) => $col !== 'id');

            // Exclud 'password' column
            if ($model === 'User') {
                $exclusions[] = 'password';
            }
        }

        // Remove the excluded columns from the provided list and return the filtered result.
        return array_diff($columns, $exclusions);
    }
}
