<?php

return [
    [
        'name' => 'Contact Manager',
        'flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Contact Settings',
        'flag' => 'contact-manager.settings',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'contact-manager.create',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'contact-manager.edit',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'contact-manager.destroy',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Contact tags',
        'flag' => 'contact-tag.index',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'contact-tag.create',
        'parent_flag' => 'contact-tag.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'contact-tag.edit',
        'parent_flag' => 'contact-tag.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'contact-tag.destroy',
        'parent_flag' => 'contact-tag.index',
    ],

    [
        'name' => 'Contact groups',
        'flag' => 'contact-group.index',
        'parent_flag' => 'contact-manager.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'contact-group.create',
        'parent_flag' => 'contact-group.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'contact-group.edit',
        'parent_flag' => 'contact-group.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'contact-group.destroy',
        'parent_flag' => 'contact-group.index',
    ],
];
