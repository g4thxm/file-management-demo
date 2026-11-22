<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'company_logo' => 'image|mimes:jpeg,png,jpg,svg|dimensions:max_width=900,max_height=150',
            'login_logo' => 'image|mimes:jpeg,png,jpg,svg',
            'login_background' => 'image|mimes:jpeg,png,jpg,svg|dimensions:min_width=1900,min_height=1200',
            'favicon' => 'image|mimes:jpeg,png,jpg,svg|dimensions:max_width=50,max_height=50'
        ];
    }

    public function messages()
    {
        return [
            'company_logo.dimensions' => 'The company logo dimensions should be 900x150 pixels or less.',
            'login_background.dimensions' => 'The login background dimensions should be at least 1900x1200 pixels.',
            'favicon.dimensions' => 'The favicon dimensions should be 50x50 pixels or less.'
        ];
    }
}
