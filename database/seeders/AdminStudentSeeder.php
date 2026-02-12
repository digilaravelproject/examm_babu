<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles Check/Create (Taaki 'Role does not exist' error na aaye)
        // Guard name 'web' default hota hai, zaroorat ho to change kar lena
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // 2. Create ADMIN User
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'], // Check condition (Is email se dhoondo)
            [
                'first_name'        => 'Super',
                'last_name'         => 'Admin',
                'user_name'         => 'admin', // Login ke liye unique username
                'mobile'            => '9999999999',
                'password'          => Hash::make('password'), // Password: password
                'is_active'         => true,
                'email_verified_at' => Carbon::now(), // Auto Verified
            ]
        );

        // Role assign karein
        $admin->assignRole($adminRole);


        // 3. Create STUDENT User
        $student = User::updateOrCreate(
            ['email' => 'student@student.com'], // Check condition
            [
                'first_name'        => 'Demo',
                'last_name'         => 'Student',
                'user_name'         => 'student',
                'mobile'            => '8888888888',
                'password'          => Hash::make('password'), // Password: password
                'is_active'         => true,
                'email_verified_at' => Carbon::now(), // Auto Verified
            ]
        );

        // Role assign karein
        $student->assignRole($studentRole);

        // Output message for confirmation
        $this->command->info('✅ Admin & Student created successfully!');
        $this->command->info('👉 Admin: admin@admin.com / password');
        $this->command->info('👉 Student: student@student.com / password');
    }
}
