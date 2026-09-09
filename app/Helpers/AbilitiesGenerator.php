<?php

namespace App\Helpers;

use Silber\Bouncer\Database\Ability;

class AbilitiesGenerator
{
    public static function generate()
    {
        $resources = [
            'projects',
            'blog-posts',
            'gallery',
            'donation',
            'members',
            'users',
            'payment-gateways',
            'website',
            'reports.notifications',
            'reports.payments',
            'reports.admin-logins',
            'acl',
            'settings.general-settings',
            'settings.system-configuration',
            'settings.server-information',
            'settings.notifications',
            'dashboard-widgets.users',
            'dashboard-widgets.members',
            'dashboard-widgets.payments',
            'dashboard-widgets.blog-posts',
            'dashboard-widgets.projects',
            'dashboard-widgets.donation-categories',
            'dashboard-widgets.galleries',
            'dashboard-widgets.page-views',
            'dashboard-charts.donations',
            'dashboard-charts.page-views',
        ];

        $actions = ['view', 'save', 'delete', 'manage'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $abilityName = "{$action}-{$resource}";

                // Check if already exists
                if (!Ability::where('name', $abilityName)->exists()) {
                    Ability::create([
                        'name'  => $abilityName,
                        'title' => ucfirst($action) . ' ' . str_replace(['.', '-'], ' ', $resource),
                    ]);
                }
            }
        }
    }
}
