<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('car_images')->insert([

            [
                'car_id' => 1,
                'image_path' => 'cars/vios1.png',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 1,
                'image_path' => 'cars/vios2.png',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 2,
                'image_path' => 'cars/fortuner1.png',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 2,
                'image_path' => 'cars/fortuner2.png',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 3,
                'image_path' => 'cars/brio1.png',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 3,
                'image_path' => 'cars/brio2.png',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 4,
                'image_path' => 'cars/hiace1.png',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 4,
                'image_path' => 'cars/hiace2.png',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 5,
                'image_path' => 'cars/ranger1.png',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'car_id' => 5,
                'image_path' => 'cars/ranger2.png',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}