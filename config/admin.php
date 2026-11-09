<?php

/*
|--------------------------------------------------------------------------
| Navigasi sidebar
|--------------------------------------------------------------------------
| Sebuah item tampil hanya bila:
|   - route-nya terdaftar (Route::has), dan
|   - pengguna lolos 'can' (bila ada), dan
|   - config 'when' bernilai true (bila ada).
| Induk tanpa anak yang tampil, dan grup tanpa item yang tampil, ikut disembunyikan.
|
| Kunci item : label, icon (nama berkas Lucide), route, can, when, badge, match, children.
| 'match'    : pola route untuk penanda aktif, mis. 'users.*'. Bawaannya {route} dan {route}.*
| Route harus tanpa parameter wajib.
*/

return [
    'nav' => [
        [
            'title' => 'Menu',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'dashboard'],
            ],
        ],
        [
            'title' => 'Administration',
            'items' => [
                ['label' => 'Users', 'icon' => 'users', 'route' => 'users.index', 'can' => 'users.view'],
                ['label' => 'Roles', 'icon' => 'shield', 'route' => 'roles.index', 'can' => 'roles.view'],
                [
                    'label' => 'Settings',
                    'icon' => 'settings',
                    'can' => 'manage-settings',
                    'children' => [
                        ['label' => 'General', 'route' => 'settings.general'],
                        ['label' => 'Mail', 'route' => 'settings.mail'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Developer',
            'items' => [
                ['label' => 'Showcase', 'icon' => 'book-open', 'route' => 'showcase', 'when' => 'admin.showcase'],
            ],
        ],
    ],

    // Hanya aktif di lingkungan local. Di staging atau production harus dinyalakan dengan sadar.
    'showcase' => env('SHOWCASE_ENABLED', env('APP_ENV') === 'local'),

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

    'api' => [
        // Endpoint tulis tidak terdaftar bila false. Nyalakan hanya bila ada klien yang benar-benar butuh.
        'writes' => env('ADMIN_API_WRITES', false),
    ],
];
