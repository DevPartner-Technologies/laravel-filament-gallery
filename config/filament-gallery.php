<?php

return [
    'disk'          => env('GALLERY_DISK', 'public'),
    'path'          => 'galleries',
    'keep_filename' => true,
    'slugify'       => true,
    'cache'         => false,
    'cache_ttl'     => 86400,
    'cache_versioning' => true,
    'cache_key' => 'filament_gallery_cache_version',
];
