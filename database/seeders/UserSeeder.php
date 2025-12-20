<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'manajer',
            'password' => Hash::make('123456'),
            'role' => 'Manajer',
            'nama' => 'Manajer Ayu Mart',
            'email' => 'manajer@ayu.com',
            'is_active' => 1
        ]);

        User::create([
            'username' => 'gudang',
            'password' => Hash::make('123456'),
            'role' => 'Gudang',
            'nama' => 'Petugas Gudang',
            'email' => 'gudang@ayu.com',
            'is_active' => 1
        ]);
    }
}
