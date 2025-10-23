<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            "store_id" => "required|exists:stores,id",
            "name" => "required|string|max:255",
            "price" => "required|numeric|min:0",
        ];
    }
    public function messages(): array
    {
        return [
            'store_id.required' => 'Vui lòng chọn cửa hàng.',
            'store_id.exists' => 'Cửa hàng không tồn tại.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'price.required' => 'Vui lòng nhập giá sản phẩm.',
            'price.numeric' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm không được âm.',
        ];
    }
}
