<?php

namespace Database\Seeders;

use App\Models\Cabang;
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

        $userCabang = User::create([
            'username' => 'cabang',
            'password' => Hash::make('123456'),
            'role' => 'Cabang',
            'nama' => 'Cabang 1',
            'email' => 'cabang1@ayu.com',
            'is_active' => 1
        ]);

        Cabang::create([
            'users_id'    => $userCabang->users_id,
            'nama_cabang' => 'Cabang 1',
            'alamat'      => 'Alamat Cabang 1',
            'kontak'      => '081234567890'
        ]);
    }
}
