<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use DB;


class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validate_location', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();

        $location_names = collect($data)->pluck('from_location')->unique();

        return $location_names->count() === 1;
        });

        Validator::extend('validate_type', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();

        $types = collect($data)->pluck('type')->unique();

        return $types->count() === 1;
        });

    }
}
