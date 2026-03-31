<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Mode Watermark
    |--------------------------------------------------------------------------
    |
    | This option controls the appearance of the "WIRODEV DEMO" watermark
    | across all screens of the application.
    |
    */
    'enabled' => env('DEMO_WATERMARK_ENABLED', true),
    'text' => env('DEMO_WATERMARK_TEXT', 'WIRODEV DEMO'),
];
