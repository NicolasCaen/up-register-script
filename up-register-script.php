<?php
/**
 * Plugin Name: UP Register Script
 * Description: Gestionnaire de scripts JavaScript avec interface d'administration
 * Version: 1.0.0
 * Author: NG1
 * Text Domain: up-register-script
 */

if (!defined('ABSPATH')) {
    exit;
}

class UpRegisterScript {
    private static $instance = null;
    private $default_scripts = [
        'gsap' => [
            'handle' => 'gsap',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',
            'deps' => [],
            'ver' => '3.12.2',
            'in_footer' => true
        ],
        'scrolltrigger' => [
            'handle' => 'scrolltrigger',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js',
            'deps' => ['gsap'],
            'ver' => '3.12.2',
            'in_footer' => true
        ],
        'aos' => [
            'handle' => 'aos',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js',
            'deps' => [],
            'ver' => '2.3.4',
            'in_footer' => true
        ],
        'slick' => [
            'handle' => 'slick',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js',
            'deps' => ['jquery'],
            'ver' => '1.8.1',
            'in_footer' => true
        ],
        'fancybox' => [
            'handle' => 'fancybox',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
            'deps' => ['jquery'],
            'ver' => '3.5.7',
            'in_footer' => true
        ],
        'datatables' => [
            'handle' => 'datatables',
            'src' => 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
            'deps' => ['jquery'],
            'ver' => '1.13.7',
            'in_footer' => true
        ]
    ];

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
        
        // Activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    public function activate() {
        // Initialiser les scripts par défaut s'ils n'existent pas déjà
        if (!get_option('up_register_scripts')) {
            update_option('up_register_scripts', $this->default_scripts);
        }
    }

    public function addAdminMenu() {
        add_menu_page(
            __('UP Register Script', 'up-register-script'),
            __('UP Scripts', 'up-register-script'),
            'manage_options',
            'up-register-script',
            [$this, 'renderAdminPage'],
            'dashicons-admin-generic'
        );
    }

    public function registerSettings() {
        register_setting('up_register_script_settings', 'up_register_scripts');
    }

    public function registerScripts() {
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        
        foreach ($scripts as $script) {
            wp_register_script(
                $script['handle'],
                $script['src'],
                $script['deps'],
                $script['ver'],
                $script['in_footer']
            );
        }
    }

    public function renderAdminPage() {
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        ?>
        <div class="wrap">
            <h1><?php _e('UP Register Script Manager', 'up-register-script'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('up_register_script_settings'); ?>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Handle</th>
                            <th>Source</th>
                            <th>Version</th>
                            <th>Dépendances</th>
                            <th>Footer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scripts as $key => $script) : ?>
                            <tr>
                                <td>
                                    <input type="text" 
                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][handle]" 
                                           value="<?php echo esc_attr($script['handle']); ?>">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][src]" 
                                           value="<?php echo esc_attr($script['src']); ?>"
                                           style="width: 100%;">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][ver]" 
                                           value="<?php echo esc_attr($script['ver']); ?>">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][deps]" 
                                           value="<?php echo esc_attr(implode(',', $script['deps'])); ?>"
                                           placeholder="jquery,gsap,...">
                                </td>
                                <td>
                                    <input type="checkbox" 
                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][in_footer]" 
                                           <?php checked($script['in_footer']); ?>>
                                </td>
                                <td>
                                    <button type="button" class="button delete-script" 
                                            data-key="<?php echo esc_attr($key); ?>">
                                        <?php _e('Supprimer', 'up-register-script'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p>
                    <button type="button" class="button button-secondary" id="add-script">
                        <?php _e('Ajouter un script', 'up-register-script'); ?>
                    </button>
                    <?php submit_button(); ?>
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#add-script').on('click', function() {
                var newRow = `
                    <tr>
                        <td><input type="text" name="up_register_scripts[new_${Date.now()}][handle]" value=""></td>
                        <td><input type="text" name="up_register_scripts[new_${Date.now()}][src]" value="" style="width: 100%;"></td>
                        <td><input type="text" name="up_register_scripts[new_${Date.now()}][ver]" value=""></td>
                        <td><input type="text" name="up_register_scripts[new_${Date.now()}][deps]" value="" placeholder="jquery,gsap,..."></td>
                        <td><input type="checkbox" name="up_register_scripts[new_${Date.now()}][in_footer]" checked></td>
                        <td><button type="button" class="button delete-script">Supprimer</button></td>
                    </tr>
                `;
                $('tbody').append(newRow);
            });

            $(document).on('click', '.delete-script', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }
}

// Initialisation
UpRegisterScript::getInstance();
