<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'printing_color_mark_json' => 'nullable|json',
            'printing_color_print_json' => 'nullable|json',
            'engraving' => 'nullable|string|max:255',
              'is_specification' => 'required|in:yes,no',
            'add_accessories_data' => 'nullable|json',
            'remove_accessories_data' => 'nullable|json',
            // 'unique_code' => 'required|string|max:255|unique:customizations,unique_code',

            'standard_code_id' => 'required|exists:standard_codes,id',

            // Specifications array validation
            'specifications' => 'nullable|array',
            'specifications.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'specifications.*.note' => 'nullable|string|max:255',
            'specifications.*.capacity' => 'nullable|string|max:255',
            'specifications.*.neck_size' => 'nullable|string|max:255',
            'specifications.*.item_name'         => 'nullable|string|max:255',
            'specifications.*.item_description'  => 'nullable|string',
            'specifications.*.remarks'           => 'nullable|string|max:255',
            'specifications.*.vendor_name'       => 'nullable|string|max:255',
            'specifications.*.pack_size'         => 'nullable|string|max:255',
            'specifications.*.moq'               => 'nullable|string|max:255',
        ];
    }
}
