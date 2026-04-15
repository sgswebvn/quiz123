<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            QuestionSeeder::class,
            AdminSeeder::class,  // Gọi seeder tạo câu hỏi
        ]);
    }
}