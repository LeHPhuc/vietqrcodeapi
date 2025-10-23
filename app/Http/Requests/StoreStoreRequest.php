<?php

namespace App\Http\Requests;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:stores,name',
            'user_id' => 'required|exists:users,id',
            'bank_code' => 'required|string|max:20',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên cửa hàng.',
            'name.unique' => 'Tên cửa hàng đã tồn tại.',
            'user_id.required' => 'Vui lòng chọn chủ cửa hàng.',
            'user_id.exists' => 'Chủ cửa hàng không tồn tại.',
            'bank_code.required' => 'Vui lòng nhập mã ngân hàng.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản ngân hàng.',
            'bank_account_name.required' => 'Vui lòng nhập tên chủ tài khoản ngân hàng.',
        ];
    }
}
