<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $page             = new Page();
        $page->title      = 'Home';
        $page->slug       = 'home';
        $page->is_default = true;
        $page->sections   = [];
        $page->save();

        $page             = new Page();
        $page->title      = 'Contact Us';
        $page->slug       = 'contact-us';
        $page->is_default = true;
        $page->save();

        $page             = new Page();
        $page->title      = 'About';
        $page->slug       = 'about';
        $page->is_default = true;
        $page->save();
    }
}
