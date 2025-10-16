<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'store_id'               => 'required|exists:stores,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'note'                   => 'nullable|string',
        ];
    }
    public function messages(): array 
    { 
        return [ 
             'store_id.required'          => 'Vui lòng chọn cửa hàng.',
            'store_id.exists'            => 'Cửa hàng không tồn tại.',
            'items.required'             => 'danh sách sản phẩm không được để trống.',
            'items.array'                => 'Danh sách sản phẩm phải là mảng.',
            'items.min'                  => 'Đơn hàng phải có ít nhất 1 sản phẩm.',
            'items.*.product_id.required'=> 'Thiếu id sản phẩm.',
            'items.*.product_id.exists'  => 'Sản phẩm không tồn tại.',
            'items.*.quantity.required'  => 'Thiếu số lượng cho một sản phẩm.',
            'items.*.quantity.integer'   => 'Số lượng phải là số nguyên.',
            'items.*.quantity.min'       => 'Số lượng tối thiểu là :min.',
            'note.string'                => 'Ghi chú không hợp lệ.',
     ]; 
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $items = $this->input('items', []);
            $storeId = $this->input('store_id');
            foreach ($items as $i => $it) {
                if (!isset($it['product_id']) || !isset($it['quantity'])) continue;
                $product = \App\Models\Product::where('store_id', $storeId)->find($it['product_id']);
                if (!$product) $v->errors()->add("items.$i.product_id","Sản phẩm không thuộc cửa hàng đã chọn.");
            }
        });
    }
}
