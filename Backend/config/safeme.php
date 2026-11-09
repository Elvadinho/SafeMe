<?php

// All SafeMe settings in one place.
// env() is only read in config files: after `php artisan config:cache`,
// env() returns null everywhere else, so the code reads config('safeme....').
return [

    'super_admin' => [
        'name'     => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'email'    => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],

    // Private disk = storage/app/private. Nothing here is reachable by URL:
    // files are only served by controllers that check permissions first.
    'disk' => 'local',

    'uploads' => [
        'max_photo_kb'    => 5120, // 5 MB
        'max_document_kb' => 8192, // 8 MB (scanned PDF)
        'max_documents'   => 4,
    ],

    // Rough bounding box of Cameroon. Rejects 0,0 and typos like 38.48 instead of 3.848.
    'bounds' => [
        'lat' => [1.6, 13.1],
        'lng' => [8.4, 16.2],
    ],

    // Search zone of a missing child: starts small and grows with time.
    'search_zone' => [
        'start_km'    => 1,
        'km_per_hour' => 2,
        'max_km'      => 50,
    ],

    'rate_limits' => [
        'reports_per_ip_per_hour'   => 5,
        'reports_per_phone_per_day' => 3,
        'forms_per_ip_per_hour'     => 20, // sightings, found / lost documents
        'logins_per_minute'         => 5,
    ],
];
