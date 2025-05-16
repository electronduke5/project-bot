<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RarityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rarities')->insert([
            ['name' => 'Обычный', 'drop_chance' => 0.5, 'points' => 25,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Редкий', 'drop_chance' => 0.25, 'points' => 55,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Сверхредкий', 'drop_chance' => 0.15, 'points' => 80,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Эпический', 'drop_chance' => 0.06, 'points' => 150,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Мифический', 'drop_chance' => 0.03, 'points' => 280,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Легендарный', 'drop_chance' => 0.01, 'points' => 750,'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
