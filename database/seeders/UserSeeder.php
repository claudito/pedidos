<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@win.com.pe'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
            ]
        );

        $security = User::query()->updateOrCreate(
            ['email' => 'seguridad@win.com.pe'],
            [
                'name' => 'Analista de Seguridad',
                'password' => Hash::make('password'),
            ]
        );

        $sales = User::query()->updateOrCreate(
            ['email' => 'ventas@win.com.pe'],
            [
                'name' => 'Ejecutivo de Ventas',
                'password' => Hash::make('password'),
            ]
        );

        $warehouse = User::query()->updateOrCreate(
            ['email' => 'almacen@win.com.pe'],
            [
                'name' => 'Supervisor de Almacen',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['SuperAdmin']);
        $security->syncRoles(['Seguridad']);
        $sales->syncRoles(['Ventas']);
        $warehouse->syncRoles(['Almacen']);
    }
}
