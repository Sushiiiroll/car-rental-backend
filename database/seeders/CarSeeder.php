<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('cars')->insert([

[
'id' => 1,
'name' => 'Toyota Vios',
'brand' => 'Toyota',
'model' => 'Vios',
'year' => 2022,
'color' => 'White',
'plate_number' => 'ABC1234',
'category_id' => 1,
'seats' => 5,
'transmission' => 'auto',
'fuel_type' => 'gasoline',
'price_per_day' => 2500,
'mileage' => 15000,
'description' => 'Reliable sedan perfect for city driving.',
'is_available' => true,
'created_at' => now(),
'updated_at' => now(),
],

[
'id' => 2,
'name' => 'Toyota Fortuner',
'brand' => 'Toyota',
'model' => 'Fortuner',
'year' => 2023,
'color' => 'Black',
'plate_number' => 'DEF5678',
'category_id' => 2,
'seats' => 7,
'transmission' => 'auto',
'fuel_type' => 'diesel',
'price_per_day' => 4500,
'mileage' => 8000,
'description' => 'Spacious SUV ideal for family trips.',
'is_available' => true,
'created_at' => now(),
'updated_at' => now(),
],

[
'id' => 3,
'name' => 'Honda Brio',
'brand' => 'Honda',
'model' => 'Brio',
'year' => 2021,
'color' => 'Red',
'plate_number' => 'GHI9012',
'category_id' => 3,
'seats' => 5,
'transmission' => 'manual',
'fuel_type' => 'gasoline',
'price_per_day' => 1800,
'mileage' => 20000,
'description' => 'Compact hatchback with great fuel efficiency.',
'is_available' => true,
'created_at' => now(),
'updated_at' => now(),
],

[
'id' => 4,
'name' => 'Toyota Hiace',
'brand' => 'Toyota',
'model' => 'Hiace',
'year' => 2022,
'color' => 'Silver',
'plate_number' => 'JKL3456',
'category_id' => 4,
'seats' => 12,
'transmission' => 'manual',
'fuel_type' => 'diesel',
'price_per_day' => 5000,
'mileage' => 12000,
'description' => 'Large van suitable for group transport.',
'is_available' => true,
'created_at' => now(),
'updated_at' => now(),
],

[
'id' => 5,
'name' => 'Ford Ranger',
'brand' => 'Ford',
'model' => 'Ranger',
'year' => 2023,
'color' => 'Blue',
'plate_number' => 'MNO7890',
'category_id' => 5,
'seats' => 5,
'transmission' => 'auto',
'fuel_type' => 'diesel',
'price_per_day' => 4200,
'mileage' => 9000,
'description' => 'Powerful pickup truck for heavy duty use.',
'is_available' => true,
'created_at' => now(),
'updated_at' => now(),
],

]);
    }
}
