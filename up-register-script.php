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
        $this->init();
    }

    public function init() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
        add_action('wp_ajax_export_scripts_php', [$this, 'exportScriptsPhp']);
        add_action('wp_ajax_export_scripts_json', [$this, 'exportScriptsJson']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        // Ajouter l'action pour la réinitialisation
        add_action('admin_post_reset_scripts', [$this, 'resetScripts']);
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
        // Afficher le message de succès si nécessaire
        if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Configuration réinitialisée avec succès.', 'up-register-script') . 
                 '</p></div>';
        }
        
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        ?>
        <div class="wrap">
            <h1><?php _e('UP Register Script Manager', 'up-register-script'); ?></h1>
            
            <!-- Bouton de réinitialisation -->
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" 
                  style="margin-bottom: 20px;" 
                  onsubmit="return confirm('<?php _e('Êtes-vous sûr de vouloir réinitialiser la configuration ?', 'up-register-script'); ?>');">
                <?php wp_nonce_field('reset_scripts'); ?>
                <input type="hidden" name="action" value="reset_scripts">
                <button type="submit" class="button button-secondary">
                    <?php _e('Réinitialiser aux valeurs par défaut', 'up-register-script'); ?>
                </button>
            </form>

            <div class="nav-tab-wrapper">
                <a href="#scripts" class="nav-tab nav-tab-active"><?php _e('Scripts', 'up-register-script'); ?></a>
                <a href="#export" class="nav-tab"><?php _e('Export', 'up-register-script'); ?></a>
            </div>

            <div id="scripts" class="tab-content active">
                <form method="post" action="options.php" enctype="multipart/form-data">
                    <?php settings_fields('up_register_script_settings'); ?>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Handle</th>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Version</th>
                                <th>Dépendances</th>
                                <th>Footer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scripts as $key => $script) : 
                                $is_file = isset($script['is_file']) && $script['is_file'];
                                $local_path = isset($script['local_path']) ? $script['local_path'] : '';
                            ?>
                                <tr>
                                    <td>
                                        <input type="text" 
                                               name="up_register_scripts[<?php echo esc_attr($key); ?>][handle]" 
                                               value="<?php echo esc_attr($script['handle']); ?>">
                                    </td>
                                    <td>
                                        <div class="script-source">
                                            <input type="text" 
                                                   class="script-url <?php echo $is_file ? 'hidden' : ''; ?>"
                                                   name="up_register_scripts[<?php echo esc_attr($key); ?>][src]" 
                                                   value="<?php echo esc_attr($script['src']); ?>"
                                                   style="width: 100%;">
                                            <input type="file" 
                                                   class="script-file <?php echo !$is_file ? 'hidden' : ''; ?>"
                                                   name="script_file_<?php echo esc_attr($key); ?>"
                                                   accept=".js">
                                            <input type="hidden" 
                                                   name="up_register_scripts[<?php echo esc_attr($key); ?>][local_path]"
                                                   value="<?php echo esc_attr($local_path); ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <select name="up_register_scripts[<?php echo esc_attr($key); ?>][is_file]" 
                                                class="source-type">
                                            <option value="0" <?php selected(!$is_file); ?>>URL</option>
                                            <option value="1" <?php selected($is_file); ?>>Fichier</option>
                                        </select>
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

            <div id="export" class="tab-content">
                <h2><?php _e('Exporter les scripts', 'up-register-script'); ?></h2>
                <p><?php _e('Choisissez le format d\'export :', 'up-register-script'); ?></p>
                
                <button type="button" class="button button-primary export-php">
                    <?php _e('Exporter en PHP', 'up-register-script'); ?>
                </button>
                
                <button type="button" class="button button-secondary export-json">
                    <?php _e('Exporter en JSON', 'up-register-script'); ?>
                </button>

                <div id="export-preview" style="margin-top: 20px;">
                    <pre><code></code></pre>
                </div>
            </div>
        </div>

        <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .hidden { display: none; }
        #export-preview {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 4px;
        }
        #export-preview pre {
            margin: 0;
            white-space: pre-wrap;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Gestion des onglets
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                const target = $(this).attr('href').substring(1);
                $('.tab-content').removeClass('active');
                $('#' + target).addClass('active');
            });

            // Gestion du type de source
            $(document).on('change', '.source-type', function() {
                const isFile = $(this).val() === '1';
                const row = $(this).closest('tr');
                row.find('.script-url').toggleClass('hidden', isFile);
                row.find('.script-file').toggleClass('hidden', !isFile);
            });

            // Ajout d'un nouveau script
            $('#add-script').on('click', function() {
                const timestamp = Date.now();
                const newRow = `
                    <tr>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][handle]" value=""></td>
                        <td>
                            <div class="script-source">
                                <input type="text" class="script-url" name="up_register_scripts[new_${timestamp}][src]" value="" style="width: 100%;">
                                <input type="file" class="script-file hidden" name="script_file_new_${timestamp}" accept=".js">
                                <input type="hidden" name="up_register_scripts[new_${timestamp}][local_path]" value="">
                            </div>
                        </td>
                        <td>
                            <select name="up_register_scripts[new_${timestamp}][is_file]" class="source-type">
                                <option value="0">URL</option>
                                <option value="1">Fichier</option>
                            </select>
                        </td>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][ver]" value=""></td>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][deps]" value="" placeholder="jquery,gsap,..."></td>
                        <td><input type="checkbox" name="up_register_scripts[new_${timestamp}][in_footer]" checked></td>
                        <td><button type="button" class="button delete-script">Supprimer</button></td>
                    </tr>
                `;
                $('tbody').append(newRow);
            });

            // Suppression d'un script
            $(document).on('click', '.delete-script', function() {
                $(this).closest('tr').remove();
            });

            // Export en PHP
            $('.export-php').on('click', function() {
                $.post(ajaxurl, {
                    action: 'export_scripts_php',
                    _ajax_nonce: '<?php echo wp_create_nonce("export_scripts"); ?>'
                }, function(response) {
                    $('#export-preview code').text(response);
                });
            });

            // Export en JSON
            $('.export-json').on('click', function() {
                $.post(ajaxurl, {
                    action: 'export_scripts_json',
                    _ajax_nonce: '<?php echo wp_create_nonce("export_scripts"); ?>'
                }, function(response) {
                    $('#export-preview code').text(JSON.stringify(response, null, 2));
                });
            });
        });
        </script>
        <?php
    }


    public function exportScriptsPhp() {
        check_ajax_referer('export_scripts');
        
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        $php = "<?php\n\n";
        $php .= "function register_custom_scripts() {\n";
        
        foreach ($scripts as $script) {
            $deps = implode("', '", $script['deps']);
            $deps = !empty($deps) ? "array('" . $deps . "')" : "array()";
            
            if (isset($script['is_file']) && $script['is_file']) {
                $php .= "    wp_register_script('{$script['handle']}', get_stylesheet_directory_uri() . '{$script['local_path']}', {$deps}, '{$script['ver']}', " . ($script['in_footer'] ? 'true' : 'false') . ");\n";
            } else {
                $php .= "    wp_register_script('{$script['handle']}', '{$script['src']}', {$deps}, '{$script['ver']}', " . ($script['in_footer'] ? 'true' : 'false') . ");\n";
            }
        }
        
        $php .= "}\n";
        $php .= "add_action('wp_enqueue_scripts', 'register_custom_scripts');\n";
        
        echo $php;
        wp_die();
    }

    public function exportScriptsJson() {
        check_ajax_referer('export_scripts');
        wp_send_json(get_option('up_register_scripts', $this->default_scripts));
    }

    // Gestion du téléchargement des fichiers
    public function handleFileUpload($script_data) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($_FILES['file'], $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $script_data['src'] = $movefile['url'];
            $script_data['local_path'] = str_replace(get_stylesheet_directory_uri(), '', $movefile['url']);
        }

        return $script_data;
    }

    public function displayDebugInfo() {
        if (!current_user_can('manage_options')) return;
        
        $scripts = get_option('up_register_scripts');
        if ($scripts === false) {
            echo '<div class="notice notice-error"><p>Erreur: Impossible de récupérer les scripts enregistrés</p></div>';
        }
    }

    public function resetScripts() {
        // Vérifier le nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'reset_scripts')) {
            wp_die('Action non autorisée', 'Erreur', ['response' => 403]);
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die('Permission refusée', 'Erreur', ['response' => 403]);
        }

        // Réinitialiser avec les valeurs par défaut
        update_option('up_register_scripts', $this->default_scripts);

        // Rediriger avec un message
        wp_redirect(add_query_arg(
            ['page' => 'up-register-script', 'reset' => 'success'],
            admin_url('admin.php')
        ));
        exit;
    }
}

// Initialisation
add_action('plugins_loaded', function() {
    UpRegisterScript::getInstance();
});
