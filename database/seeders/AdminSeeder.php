<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();

        if ($user) {
            $user->is_admin = 1;
            $user->save();
        }

        // (optional) tạo tài khoản admin nếu chưa có
        User::updateOrCreate(
            ['email' => 'admin@quiz.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456'),
                'is_admin' => 1
            ]
        );
    }
}