<?php
return [
    'gsap-lightbox' => [
        'handle' => 'ng1-gsap-lightbox',
        'src' => plugins_url('gsap-library/lightbox/assets/js/function.js', dirname(plugin_dir_path(__FILE__))),
        'deps' => ['gsap'],
        'ver' => '1.0.0',
        'in_footer' => true,
        'type' => 'js',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ],
    'gsap-lightbox-style' => [
        'handle' => 'ng1-gsap-lightbox',
        'src' => plugins_url('gsap-library/lightbox/assets/css/style.css', dirname(plugin_dir_path(__FILE__))),
        'deps' => [],
        'ver' => '1.0.0',
        'type' => 'css',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ]
];
