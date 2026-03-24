<?php

return [
    [
        'id' => 1,
        'email' => 'admin@centre.cat',
        // password: 1234
        'password' => password_hash('1234', PASSWORD_DEFAULT),
        'name' => 'Centre Admin',
        'role' => 'centre'
    ],
    [
        'id' => 2,
        'email' => 'empresa@empresa.cat',
        // password: 1234
        'password' => password_hash('1234', PASSWORD_DEFAULT),
        'name' => 'Empresa Demo',
        'role' => 'empresa'
    ]
];
