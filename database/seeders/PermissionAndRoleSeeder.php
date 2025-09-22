<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menyimpan permissions ke dalam variabel
        $userPermissions = [
            'view-any-user',
            'view-user',
            'create-user',
            'update-user',
            'delete-user',
        ];
        $studentPermissions = [
            'view-any-student',
            'view-student',
            'create-student',
            'update-student',
            'delete-student',
        ];
        $applicationPermissions = [
            'view-any-application',
            'view-application',
            'create-application',
            'update-application',
            'delete-application',

            'request-verify-application',
            'verify-application',
            'reject-application',
        ];


        // Menggabungkan semua permissions ke dalam satu array untuk proses pembuatan permissions
        $allPermissions = array_merge(
            $userPermissions,
            $studentPermissions,
            $applicationPermissions
        );

        // Membuat semua permissions
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Menyimpan roles ke dalam variabel
        $roles = [
            'admin' => Permission::all(),
            'student' => Permission::all()
        ];

        // Membuat roles dan memberikan permissions
        foreach ($roles as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($permissions);
        }
    }
}
