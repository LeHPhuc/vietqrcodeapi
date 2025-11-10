<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'duration_days' => [
                'required',         
                'integer',
                'min:30',
                'max:365',           
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên gói là bắt buộc.',
            'name.string'   => 'Tên gói phải là chuỗi.',
            'name.max'      => 'Tên gói tối đa 150 ký tự.',

            'price.required' => 'Giá là bắt buộc.',
            'price.numeric'  => 'Giá phải là số.',
            'price.min'      => 'Giá không được âm.',

            'duration_days.required' => 'Thời hạn (ngày) là bắt buộc.',
            'duration_days.integer' => 'Thời hạn (ngày) phải là số nguyên.',
            'duration_days.min'     => 'Thời hạn tối thiểu là 1 ngày.',
            'duration_days.max'     => 'Thời hạn tối đa là 3650 ngày.',
        ];
    }
}
