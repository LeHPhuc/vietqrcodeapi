<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name'     => ['sometimes','string','max:150'],
            'price'    => ['sometimes','numeric','min:0'],
            'store_id' => ['sometimes','integer','exists:stores,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên sản phẩm không được vượt quá 150 ký tự.',

            'price.numeric' => 'Giá sản phẩm phải là một số hợp lệ.',
            'price.min' => 'Giá sản phẩm không được nhỏ hơn 0.',

            'store_id.integer' => 'ID cửa hàng phải là một số nguyên hợp lệ.',
            'store_id.exists' => 'Cửa hàng với ID đã cho không tồn tại.',
        ];
    }
}
