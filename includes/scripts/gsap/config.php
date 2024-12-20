<?php
return [
    'gsap' => [
        'handle' => 'gsap',
        'src' => 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',
        'deps' => [],
        'ver' => '3.12.2',
        'in_footer' => true,
        'type' => 'js',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ],
    'scrolltrigger' => [
        'handle' => 'scrolltrigger',
        'src' => 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js',
        'deps' => ['gsap'],
        'ver' => '3.12.2',
        'in_footer' => true,
        'type' => 'js',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ]
]; 