<?php

return [
    'contact_sources' => [
        /**
         * When Enabled, when a user submits contact form
         * a new contact will be added to the manager.
         */
        'contact_form' => env('ENABLE_CONTACT_FORM_CONTACT_IMPORT', true),
    ],
];
