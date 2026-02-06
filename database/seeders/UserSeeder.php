<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Integrasi\TbCabang;
use App\Models\Supplier;
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
            'username' => 'owner',
            'password' => Hash::make('123456'),
            'role' => 'Owner',
            'nama' => 'Owner Ayu Mart',
            'email' => 'owner@ayu.com',
            'is_active' => 1
        ]);

        User::create([
            'username' => 'purchasing',
            'password' => Hash::make('123456'),
            'role' => 'Purchasing',
            'nama' => 'Purchasing Ayu Mart',
            'email' => 'purchasing@ayu.com',
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

        TbCabang::create([
            'users_id'    => $userCabang->users_id,
            'nama_cabang' => 'Cabang 1',
            'alamat'      => 'Alamat Cabang 1',
            'kontak'      => '081234567890'
        ]);

        User::create([
            'username' => 'kurir',
            'password' => Hash::make('123456'),
            'role' => 'Kurir',
            'nama' => 'Kurir Ayu Mart',
            'email' => 'kurir@ayu.com',
            'is_active' => 1
        ]);

        $userSupplier = User::create([
            'username' => 'supplier',
            'password' => Hash::make('123456'),
            'role' => 'Supplier',
            'nama' => 'Supplier Ayu Mart',
            'email' => 'supplier@ayu.com',
            'is_active' => 1
        ]);
        Supplier::create([
            'users_id'       => $userSupplier->users_id,
            'nama_supplier'  => 'Supplier Ayu Mart',
            'alamat'         => 'Alamat Supplier',
            'kontak'         => '089876543210',
            'status_supplier' => 'aktif'
        ]);
    }
}
