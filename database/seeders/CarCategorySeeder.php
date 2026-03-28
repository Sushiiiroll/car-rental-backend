<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Sedan',
                'slug' => 'sedan',
                'description' => 'Comfortable cars perfect for city driving and daily commuting.',
                'icon' => 'sedan-icon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'SUV',
                'slug' => 'suv',
                'description' => 'Sport Utility Vehicles offering space, power, and off-road capability.',
                'icon' => 'suv-icon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Hatchback',
                'slug' => 'hatchback',
                'description' => 'Compact cars with great fuel efficiency and easy maneuverability.',
                'icon' => 'hatchback-icon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Van',
                'slug' => 'van',
                'description' => 'Large vehicles ideal for transporting groups or cargo.',
                'icon' => 'van-icon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Pickup',
                'slug' => 'pickup',
                'description' => 'Powerful trucks designed for heavy-duty transport and work.',
                'icon' => 'pickup-icon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}