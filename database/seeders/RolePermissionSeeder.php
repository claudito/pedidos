<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'roles',
            'permisos',
            'usuarios',
            'clientes',
            'productos',
            'pedidos',
            'seguimientos',
        ]);

        Permission::query()
            ->whereNotIn('name', $permissions)
            ->delete();

        $permissions->each(function (string $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        });

        $superAdminRole = Role::findOrCreate('SuperAdmin', 'web');
        $adminRole = Role::findOrCreate('Administrador', 'web');
        $securityRole = Role::findOrCreate('Seguridad', 'web');
        $warehouseRole = Role::findOrCreate('Almacen', 'web');
        $salesRole = Role::findOrCreate('Ventas', 'web');

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions([
            'clientes',
            'productos',
            'pedidos',
            'seguimientos',
        ]);
        $securityRole->syncPermissions([
            'roles',
            'permisos',
            'usuarios',
        ]);
        $warehouseRole->syncPermissions([
            'productos',
            'pedidos',
            'seguimientos',
        ]);
        $salesRole->syncPermissions([
            'clientes',
            'pedidos',
            'seguimientos',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
