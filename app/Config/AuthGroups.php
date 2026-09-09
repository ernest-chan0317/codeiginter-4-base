<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'user';

    /**
     * Define project-specific groups when you fork this base project.
     */
    public array $groups = [
        'admin' => [],
        'user'  => [],
    ];

    public array $permissions = [
        'admin.access'   => 'Can access the admin area',
        'admin.settings' => 'Can access site settings',
        'users.manage'   => 'Can manage users',
    ];

    public array $matrix = [
        'admin' => [
            'admin.*',
            'users.*',
        ],
        'user' => [],
    ];
}
