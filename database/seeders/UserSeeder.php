<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Rayhan Mahasiswa',
            'email' => 'mahasiswa@test.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'stall_id' => null,
        ]);

        $penjual = [
            ['name' => 'Pak Gembus',          'email' => 'gembus@test.com',       'stall_id' => 1],
            ['name' => 'Teh Puncak',          'email' => 'tehpuncak@test.com',    'stall_id' => 2],
            ['name' => 'Mang Udin',           'email' => 'udin@test.com',         'stall_id' => 3],
            ['name' => 'Bandung Mie',         'email' => 'bandung@test.com',      'stall_id' => 4],
            ['name' => 'Ajo Sate',            'email' => 'ajo@test.com',          'stall_id' => 5],
            ['name' => 'Bang Jago',           'email' => 'jago@test.com',         'stall_id' => 6],
            ['name' => 'Nyak Es Campur',      'email' => 'nyak@test.com',         'stall_id' => 7],
            ['name' => 'Ibu Pisgor',          'email' => 'ibu@test.com',          'stall_id' => 8],
            ['name' => 'Haji Betawi',         'email' => 'haji@test.com',         'stall_id' => 9],
            ['name' => 'Kopi Senja',          'email' => 'senja@test.com',        'stall_id' => 10],
        ];

        foreach ($penjual as $p) {
            User::create([
                'name' => $p['name'],
                'email' => $p['email'],
                'password' => Hash::make('password'),
                'role' => 'penjual',
                'stall_id' => $p['stall_id'],
            ]);
        }
    }
}
