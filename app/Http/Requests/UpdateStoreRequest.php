<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
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
            'name'                => ['sometimes','string','max:150',\Illuminate\Validation\Rule::unique('stores', 'name')->ignore($this->route('store')),],
            'bank_code'           => ['sometimes','string','max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'bank_account_number' => ['sometimes','string'],
            'bank_account_name'   => ['sometimes','string','max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Tên cửa hàng này đã tồn tại. Vui lòng chọn tên khác.',
            'name.string' => 'Tên cửa hàng phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên cửa hàng không được vượt quá 150 ký tự.',

            'bank_code.string' => 'Mã ngân hàng phải là chuỗi hợp lệ.',
            'bank_code.max' => 'Mã ngân hàng không được vượt quá 10 ký tự.',
            'bank_code.regex' => 'Mã ngân hàng chỉ được chứa chữ cái và số, không dấu hoặc ký tự đặc biệt.',

            'bank_account_number.string' => 'Số tài khoản phải là chuỗi ký tự số.',

            'bank_account_name.string' => 'Tên chủ tài khoản phải là chuỗi ký tự hợp lệ.',
            'bank_account_name.max' => 'Tên chủ tài khoản không được vượt quá 100 ký tự.',
        ];
    }
}
