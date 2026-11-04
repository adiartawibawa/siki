<?php

return [
    // Tampil hanya di luar production, supaya halaman ini tidak ikut terbit
    // di aplikasi turunan yang lupa menggantinya.
    'landing' => env('STARTER_LANDING', env('APP_ENV') !== 'production'),

    'tagline' => 'Start the next app from a working login.',

    // Ubah status ini setiap kali modul selesai atau berubah.
    'modules' => [
        ['Register and log in',       'Sign up and sign in pages with Google as an option.',          'ready'],
        ['Email verification',        'Six-digit code entry with resend.',                            'ready'],
        ['Forgot and reset password', 'Request a link, then set a new password.',                     'ready'],
        ['Confirm password',          'Re-check the password before sensitive actions.',              'ready'],
        ['Two-factor challenge',      'Code entry after login.',                                      'planned'],
        ['Admin panel',               'Layout and components for back-office screens.',               'planned'],
    ],

    'replace_before_shipping' => [
        'App name and logo (APP_NAME, the mark in the auth shell)',
        'Illustrations in public/images (auth-*.svg)',
        'Terms and conditions and privacy links',
        'Copy on the auth pages, written for your users',
        'This page, or set STARTER_LANDING=false',
    ],
];
