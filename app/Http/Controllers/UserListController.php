<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserListController extends Controller
{
    // GET /users/names  -> trả về danh sách tên user
    public function names()
    {
        // chỉ lấy cột 'name' và trả về mảng thuần
        $names = User::query()->orderBy('name')->pluck('name');

        return response()->json([
            'count' => $names->count(),
            'names' => $names,
        ]);
    }
}
