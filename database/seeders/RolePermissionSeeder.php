<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset tabel
        //DB::table('permission_role')->truncate();
        //DB::table('role_user')->truncate();
        //DB::table('permissions')->truncate();
        //DB::table('roles')->truncate();

        // Role
        $adminRoleId = DB::table('roles')->insertGetId(['name' => 'admin']);
        $userRoleId = DB::table('roles')->insertGetId(['name' => 'user']);

        // Permissions
        $permissions = [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
        ];

        $permissionIds = [];
        foreach ($permissions as $perm) {
            $id = DB::table('permissions')->insertGetId(['name' => $perm]);
            $permissionIds[$perm] = $id;
        }

        // Assign all permissions to admin
        foreach ($permissionIds as $permId) {
            DB::table('permission_role')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permId,
            ]);
        }

        // Only view for user
        DB::table('permission_role')->insert([
            'role_id' => $userRoleId,
            'permission_id' => $permissionIds['user.view'],
        ]);

        // Assign roles ke user (contoh)
        $admin = User::where('email', 'admin@test.com')->first();
        $user = User::where('email', 'user@test.com')->first();

        if ($admin) DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $adminRoleId]);
        if ($user) DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $userRoleId]);
    }
}
