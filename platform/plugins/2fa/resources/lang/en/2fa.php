<?php

return [
    'name' => 'Two-factor Authentication',
    'enable_success' => 'You have successfully enabled two-factor authentication.',
    'disable_success' => 'You have successfully disabled two-factor authentication.',
    'challenge_code_tutorial' => 'Please confirm access to your account by entering the authentication code provided by your authenticator application.',
    'challenge_recovery_code_tutorial' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
    'code' => 'Code',
    'recovery_code' => 'Recovery Code',
    'login' => 'Login',
    'confirm' => 'Confirm',
    'done' => 'Done',
    'next' => 'Next',
    'cancel' => 'Cancel',
    'turn_off' => 'Turn off',
    'use_recovery_code' => 'Use Recovery Code',
    'use_code' => 'Use an authentication code',
    'ask_enable_button' => 'Enable Two-factor Authentication?',
    'ask_disable_button' => 'Disable Two-factor Authentication?',
    'invalid_code' => 'The provided two factor authentication code was invalid.',
    'confirm_disable_title' => 'Turn off two-factor authentication?',
    'confirm_disable_description' => 'This feature protects your account with an extra layer of security.',
    'enter_code_to_disable' => 'Confirm your two-factor authentication code to turn it off.',
    'settings' => [
        'title' => 'Two-factor Authentication Settings',
        'description' => 'Configure your two-factor authentication settings.',
        'enable_global' => 'Enable Two-factor Authentication',
    ],
    'setup' => [
        'welcome_title' => 'Protect your account in just two steps!',
        'qrcode_title' => 'Link the app to your account',
        'done_title' => 'You’re all set',
        'welcome_tutorial_step_1' => 'Link an authentication app to your account',
        'welcome_tutorial_step_1_description' => 'Use a compatible authentication app (like Google Authenticator, Authy, Duo Mobile, 1Password, etc.) We’ll generate a QR code for you to scan.',
        'welcome_tutorial_step_2' => 'Enter the confirmation code',
        'welcome_tutorial_step_2_description' => 'Two-factor authentication will then be turned on for authentication app, which you can turn off at any time.',
        'scan_qrcode_tutorial' => 'Use your authentication app to scan this QR code. If you don’t have an authentication app on your device, you’ll need to install one now.',
        'enter_code_manually_tutorial' => 'If you can’t scan the QR code with your camera, enter the following code into the authentication app to link it to your account.',
        'cannot_scan_qrcode' => 'Can’t scan the QR code?',
        'try_scan_qrcode_again' => 'Try to scan the QR code again',
        'enter_code_tutorial' => 'Follow the instructions on the authentication app to link your account. Once the authentication app generates a confirmation code, enter it here.',
        'done_message' => 'Now you can use the mobile authentication app to get an authentication code any time you log in to your account.',
        'backup_codes_tutorial' => 'You can also use the following backup codes to log in if you lose access to your authentication app.',
    ],
];