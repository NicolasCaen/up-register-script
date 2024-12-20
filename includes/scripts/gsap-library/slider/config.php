<?php
return [
    'gsap-slider' => [
        'handle' => 'ng1-gsap-slider',
        'src' => plugins_url('gsap-library/slider/assets/js/function.js', dirname(plugin_dir_path(__FILE__))),
        'deps' => ['gsap', 'scrolltrigger'],
        'ver' => '1.0.0',
        'in_footer' => true,
        'type' => 'js',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ],
    'gsap-slider-style' => [
        'handle' => 'ng1-gsap-slider',
        'src' => plugins_url('gsap-library/slider/assets/css/style.css', dirname(plugin_dir_path(__FILE__))),
        'deps' => [],
        'ver' => '1.0.0',
        'type' => 'css',
        'load_front' => false,
        'load_admin' => false,
        'load_editor' => false
    ]
]; 