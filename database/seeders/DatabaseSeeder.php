<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset Cached Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create Permissions (Jo action perform karne hain)
        $permissions = [
            'view dashboard',
            'manage roles', // Create/Edit roles
            'view logs',    // Activity logs dekhna
            'manage users',
            'create content',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 3. Create Roles
        $roles = [
            'admin',      // Sab kuch kar sakega
            'student',    // Padhal likhai karega
            'instructor', // Course banayega
            'institute', // Institute related kaam karega
            'parent',    // Bacchon ke liye
            'guest'
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // 4. Create Default Admin User
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'), // Default Password
        ]);

        // 5. Assign Role & Permissions to Admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all()); // Admin ko saari permissions

        $adminUser->assignRole('admin');

        // Example: Student User (Testing ke liye)
        $studentUser = User::create([
            'name' => 'Rahul Student',
            'email' => 'rahul@student.com',
            'password' => Hash::make('password123'),
        ]);
        $studentUser->assignRole('student');
        $studentUser->givePermissionTo('view dashboard');
    }
}
