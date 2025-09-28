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
        $rolePermissions = [
            'view-any-role',
            'view-role',
            'create-role',
            'update-role',
            'delete-role',
        ];
        $facultyPermissions = [
            'view-any-faculty',
            'view-faculty',
            'create-faculty',
            'update-faculty',
            'delete-faculty',
        ];
        $departmentPermissions = [
            'view-any-department',
            'view-department',
            'create-department',
            'update-department',
            'delete-department',
        ];
        $provincePermissions = [
            'view-any-province',
            'view-province',
            'create-province',
            'update-province',
            'delete-province',
        ];
        $cityPermissions = [
            'view-any-city',
            'view-city',
            'create-city',
            'update-city',
            'delete-city',
        ];
        $studentPermissions = [
            'view-any-student',
            'view-student',
            'create-student',
            'update-student',
            'delete-student',
        ];

        $criteriaPermissions = [
            'view-any-criteria',
            'view-criteria',
            'create-criteria',
            'update-criteria',
            'delete-criteria',
        ];
        $scholarshipPermissions = [
            'view-any-scholarship',
            'view-scholarship',
            'create-scholarship',
            'update-scholarship',
            'delete-scholarship',
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

            'select-all-student-application',
            'verify-document-application'
        ];
        $applicationDataPermissions = [
            'view-any-application-data',
            'view-application-data',
            'create-application-data',
            'update-application-data',
            'delete-application-data',

            'verify-application-data'
        ];


        // Menggabungkan semua permissions ke dalam satu array untuk proses pembuatan permissions
        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            $facultyPermissions,
            $departmentPermissions,
            $provincePermissions,
            $cityPermissions,
            $studentPermissions,
            $criteriaPermissions,
            $scholarshipPermissions,
            $applicationPermissions,
            $applicationDataPermissions
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
