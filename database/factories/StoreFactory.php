<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Thêm dòng này

class StoreFactory extends Factory
{
    public function definition(): array
    {
        $bins = ['MB','TPB'];
        $bin  = $this->faker->randomElement($bins);
        $accountNumber = '0337367643';
        $accountName = 'LE HOANG PHUC';

        // Lấy user_id ngẫu nhiên từ bảng users
        $userIds = User::pluck('id')->toArray();

        return [
            'user_id'              => $this->faker->randomElement($userIds),
            'name'                 => 'Cửa hàng ' . $this->faker->unique()->company(),
            'bank_code'            => $bin,
            'bank_account_number'  => $accountNumber,
            'bank_account_name'    => $accountName,
        ];
    }
}