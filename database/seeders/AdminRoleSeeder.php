<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure Bouncer uses Admin model
        Bouncer::useUserModel(Admin::class);

        // Create roles
        $superAdmin = Bouncer::role()->firstOrCreate([
            'name' => 'super-admin',
            'title' => 'Super Admin'
        ]);

        $editor = Bouncer::role()->firstOrCreate([
            'name' => 'editor',
            'title' => 'Editor'
        ]);

        // Create abilities
        $manageUsers = Bouncer::ability()->firstOrCreate([
            'name' => 'manage-users',
            'title' => 'Manage all users'
        ]);

        $editArticles = Bouncer::ability()->firstOrCreate([
            'name' => 'edit-articles',
            'title' => 'Edit any article'
        ]);

        // Assign abilities to roles
        Bouncer::allow('super-admin')->to($manageUsers);
        Bouncer::allow('editor')->to($editArticles);

        // Assign role to first admin
        $admin = Admin::first();
        if ($admin) {
            Bouncer::assign($superAdmin)->to($admin);
        }
    }
}
