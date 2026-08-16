<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inherit from another theme
    |--------------------------------------------------------------------------
    */

    'inherit' => 'farmart',

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    */

    'events' => [
        'beforeRenderTheme' => function (\Botble\Theme\Theme $theme): void {
            // We can load additional child theme styling here if needed
        },
    ],
];
