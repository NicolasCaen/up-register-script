<?php
return [
    'datatables' => [
        'handle' => 'datatables',
        'src' => 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
        'deps' => ['jquery'],
        'ver' => '1.13.7',
        'in_footer' => true,
        'type' => 'js',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ],
    'datatables-style' => [
        'handle' => 'datatables',
        'src' => 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css',
        'deps' => [],
        'ver' => '1.13.7',
        'type' => 'css',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ]
]; 