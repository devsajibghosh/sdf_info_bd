<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\GeneralSetting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'ok',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $generalSetting = new GeneralSetting();
        $generalSetting->site_title = 'Site Title';
        $generalSetting->save();
        
        $user           = new User;
        $user->username = 'username';
        $user->password = Hash::make('username');
        $user->name     = 'User Name';
        $user->email    = 'user@gmail.com';
        $user->save();

        $admin           = new Admin();
        $admin->name     = 'Admin';
        $admin->username = 'admin';
        $admin->email    = 'admin@gmail.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $this->call([
            AdminRoleSeeder::class,
            DefaultPagesSeeder::class,
            ReturnRefundPolicySeeder::class,
            SslCommerzSystemAdminSeeder::class,
        ]);
    }
}
