<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            ['stall_id' => 1,  'name' => 'Ayam Penyet',           'price' => 15000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 1,  'name' => 'Tahu Goreng',           'price' => 5000,  'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 1,  'name' => 'Tempe Goreng',          'price' => 5000,  'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 2,  'name' => 'Es Teh Manis',          'price' => 5000,  'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 2,  'name' => 'Es Jeruk',              'price' => 7000,  'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 2,  'name' => 'Teh Hangat',            'price' => 3000,  'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 3,  'name' => 'Nasi Goreng Spesial',   'price' => 15000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 3,  'name' => 'Nasi Goreng Telur',     'price' => 12000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 3,  'name' => 'Nasi Goreng Mawut',     'price' => 18000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 4,  'name' => 'Mie Kocok',             'price' => 15000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 4,  'name' => 'Bakso Urat',            'price' => 12000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 4,  'name' => 'Mie Ayam',              'price' => 10000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 5,  'name' => 'Sate Padang',           'price' => 20000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 5,  'name' => 'Lontong Sayur',         'price' => 10000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 5,  'name' => 'Sate Daging',           'price' => 25000, 'category' => 'makanan', 'is_ready' => false],
            ['stall_id' => 6,  'name' => 'Martabak Telur',        'price' => 20000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 6,  'name' => 'Martabak Manis Coklat', 'price' => 25000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 6,  'name' => 'Martabak Mini',         'price' => 15000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 7,  'name' => 'Es Campur',             'price' => 10000, 'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 7,  'name' => 'Es Teler',              'price' => 12000, 'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 7,  'name' => 'Es Doger',              'price' => 10000, 'category' => 'minuman', 'is_ready' => false],
            ['stall_id' => 8,  'name' => 'Pisang Goreng',         'price' => 8000,  'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 8,  'name' => 'Singkong Goreng',       'price' => 7000,  'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 8,  'name' => 'Ubi Goreng',            'price' => 8000,  'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 9,  'name' => 'Soto Betawi',           'price' => 18000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 9,  'name' => 'Lontong Soto',          'price' => 15000, 'category' => 'makanan', 'is_ready' => true],
            ['stall_id' => 9,  'name' => 'Soto Daging',           'price' => 22000, 'category' => 'makanan', 'is_ready' => false],
            ['stall_id' => 10, 'name' => 'Kopi Susu',             'price' => 8000,  'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 10, 'name' => 'Kopi Hitam',            'price' => 5000,  'category' => 'minuman', 'is_ready' => true],
            ['stall_id' => 10, 'name' => 'Kopi Jahe',             'price' => 7000,  'category' => 'minuman', 'is_ready' => true],
        ];

        foreach ($foods as $food) {
            Food::create($food);
        }
    }
}
