<?php

return [
    'nav' => [
        [
            'title' => 'Menu',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'dashboard'],
                [
                    'label' => 'Analytics',
                    'icon' => 'chart-column',
                    'children' => [
                        ['label' => 'Overview', 'route' => 'analytics.overview'],
                        ['label' => 'Reports',  'route' => 'analytics.reports'],
                        ['label' => 'Exports',  'route' => 'analytics.exports', 'can' => 'export-data'],
                    ],
                ],
                ['label' => 'Insights', 'icon' => 'sparkles', 'route' => 'insights'],
                ['label' => 'Updates',  'icon' => 'clock', 'route' => 'updates'],
                ['label' => 'Chat',     'icon' => 'message-circle', 'route' => 'chat', 'badge' => 20],
            ],
        ],
        [
            'title' => 'General',
            'items' => [
                [
                    'label' => 'Settings',
                    'icon' => 'settings',
                    // 'can' => '',
                    'children' => [
                        ['label' => 'General', 'route' => 'settings.general'],
                        ['label' => 'Mail',    'route' => 'settings.mail'],
                    ],
                ],
                ['label' => 'Help Desk',   'icon' => 'circle-help', 'route' => 'help'],
                ['label' => 'Integration', 'icon' => 'link', 'route' => 'integrations'],
                ['label' => 'Feedback',    'icon' => 'message-square-text', 'route' => 'feedback'],
            ],
        ],
        [
            'title' => 'Administration',
            'items' => [
                ['label' => 'Users', 'icon' => 'users', 'route' => 'users.index', 'can' => 'users.view'],
                ['label' => 'Roles', 'icon' => 'shield', 'route' => 'roles.index', 'can' => 'roles.view'],
            ],
        ],
    ],

    'promo' => [
        'enabled' => true,
        'title' => 'Upgrade Pro!',
        'text' => 'Higher productivity with better organization',
        'action' => 'Upgrade Pro',
        'href' => '#',
    ],

    'initial' => [
        'email' => env('INITIAL_ADMIN_EMAIL'),
        'password' => env('INITIAL_ADMIN_PASSWORD'),
    ],

    'account' => [
        'deletable' => true,
    ],
];
