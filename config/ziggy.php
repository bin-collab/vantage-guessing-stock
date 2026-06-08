<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Filters
    |--------------------------------------------------------------------------
    |
    | Define route name patterns to exclude from Ziggy's generated routes.
    | These routes will NOT be available in the frontend JavaScript.
    |
    */

    'except' => [
        'filament.*',
        'livewire.*',
        'default-livewire.*',
    ],

];
