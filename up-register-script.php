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
        ],
        'aos' => [
            'handle' => 'aos',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js',
            'deps' => [],
            'ver' => '2.3.4',
            'in_footer' => true,
            'type' => 'js',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'aos-style' => [
            'handle' => 'aos',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css',
            'deps' => [],
            'ver' => '2.3.4',
            'type' => 'css',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'slick' => [
            'handle' => 'slick',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js',
            'deps' => ['jquery'],
            'ver' => '1.8.1',
            'in_footer' => true,
            'type' => 'js',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'slick-style' => [
            'handle' => 'slick',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css',
            'deps' => [],
            'ver' => '1.8.1',
            'type' => 'css',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'slick-theme' => [
            'handle' => 'slick-theme',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css',
            'deps' => ['slick'],
            'ver' => '1.8.1',
            'type' => 'css',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'fancybox' => [
            'handle' => 'fancybox',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
            'deps' => ['jquery'],
            'ver' => '3.5.7',
            'in_footer' => true,
            'type' => 'js',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
        'fancybox-style' => [
            'handle' => 'fancybox',
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css',
            'deps' => [],
            'ver' => '3.5.7',
            'type' => 'css',
            'load_front' => false,
            'load_admin' => false,
            'load_editor' => false
        ],
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

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init();
        // Ajouter jQuery UI
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    public function enqueueAdminScripts($hook) {
        if ('toplevel_page_up-register-script' !== $hook) {
            return;
        }
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('up-register-script-admin', plugins_url('css/admin.css', __FILE__));
    }

    public function init() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
        add_action('wp_ajax_export_scripts_php', [$this, 'exportScriptsPhp']);
        add_action('wp_ajax_export_scripts_json', [$this, 'exportScriptsJson']);
        add_action('admin_post_reset_scripts', [$this, 'resetScripts']);
        add_action('wp_ajax_save_scripts_order', [$this, 'saveScriptsOrder']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    public function activate() {
        // Initialiser les scripts par défaut s'ils n'existent pas déjà
        if (!get_option('up_register_scripts')) {
            update_option('up_register_scripts', $this->default_scripts);
        }

        // Initialiser et créer le dossier pour les scripts
        $options = get_option('up_register_script_params', [
            'auto_load' => true,
            'download_cdn' => false,
            'local_path' => '/wp-content/uploads/scripts/'
        ]);

        $this->createScriptsDirectory($options['local_path']);
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
        // Paramètres existants
        register_setting(
            'up_register_script_settings', 
            'up_register_scripts',
            [
                'sanitize_callback' => [$this, 'sanitizeScripts']
            ]
        );
        
        // Nouveaux paramètres
        register_setting(
            'up_register_script_params',
            'up_register_script_params',
            [
                'sanitize_callback' => [$this, 'sanitizeParams'],
                'default' => [
                    'auto_load' => true,
                    'download_cdn' => false,
                    'local_path' => '/wp-content/uploads/scripts/'
                ]
            ]
        );
    }

    public function sanitizeParams($input) {
        $sanitized = [
            'auto_load' => isset($input['auto_load']),
            'download_cdn' => isset($input['download_cdn']),
            'local_path' => sanitize_text_field(trailingslashit($input['local_path']))
        ];

        // Créer le nouveau dossier si le chemin a changé
        $old_options = get_option('up_register_script_params');
        if ($old_options['local_path'] !== $sanitized['local_path']) {
            $this->createScriptsDirectory($sanitized['local_path']);
        }

        return $sanitized;
    }

    private function createScriptsDirectory($path) {
        $full_path = untrailingslashit(ABSPATH) . $path;
        
        // Vérifier si le dossier existe
        if (!file_exists($full_path)) {
            // Créer le dossier avec les bonnes permissions
            $created = wp_mkdir_p($full_path);
            
            // Ajouter un fichier index.php pour la sécurité
            if ($created) {
                $index_file = $full_path . '/index.php';
                if (!file_exists($index_file)) {
                    file_put_contents($index_file, "<?php\n// Silence is golden.");
                }
                
                // Ajouter un .htaccess pour permettre l'accès aux fichiers
                $htaccess_file = $full_path . '/.htaccess';
                if (!file_exists($htaccess_file)) {
                    $htaccess_content = "Allow from all\n";
                    $htaccess_content .= "<FilesMatch \"\.(js|css)$\">\n";
                    $htaccess_content .= "    Order Allow,Deny\n";
                    $htaccess_content .= "    Allow from all\n";
                    $htaccess_content .= "</FilesMatch>";
                    file_put_contents($htaccess_file, $htaccess_content);
                }
            }
            
            // Vérifier si la création a échoué
            if (!$created) {
                add_settings_error(
                    'up_register_script_params',
                    'directory_creation_failed',
                    sprintf(
                        __('Impossible de créer le dossier %s. Vérifiez les permissions.', 'up-register-script'),
                        $path
                    )
                );
            }
        }
    }

    public function registerScripts() {
        $options = get_option('up_register_script_params');
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        
        $auto_load = isset($options['auto_load']) ? $options['auto_load'] : true;
        
        foreach ($scripts as $script) {
            // Utiliser le fichier local s'il existe, sinon l'URL d'origine
            $src = (!empty($script['local_path']) && $script['is_file']) 
                ? site_url($script['local_path']) 
                : $script['src'];

            if ($script['type'] === 'js') {
                wp_register_script(
                    $script['handle'],
                    $src,
                    $script['deps'],
                    $script['ver'],
                    $script['in_footer']
                );
                
                if ($auto_load) {
                    $this->maybeEnqueueScript($script);
                }
            } else {
                wp_register_style(
                    $script['handle'],
                    $src,
                    $script['deps'],
                    $script['ver']
                );
                
                if ($auto_load) {
                    $this->maybeEnqueueStyle($script);
                }
            }
        }
    }

    private function maybeEnqueueScript($script) {
        $is_admin = is_admin();
        $is_editor = defined('IFRAME_REQUEST') && IFRAME_REQUEST;
        
        if ((!$is_admin && !empty($script['load_front'])) ||
            ($is_admin && !empty($script['load_admin'])) ||
            ($is_editor && !empty($script['load_editor']))) {
            wp_enqueue_script($script['handle']);
        }
    }

    private function maybeEnqueueStyle($script) {
        $is_admin = is_admin();
        $is_editor = defined('IFRAME_REQUEST') && IFRAME_REQUEST;
        
        if ((!$is_admin && !empty($script['load_front'])) ||
            ($is_admin && !empty($script['load_admin'])) ||
            ($is_editor && !empty($script['load_editor']))) {
            wp_enqueue_style($script['handle']);
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
                <a href="#params" class="nav-tab"><?php _e('Paramètres', 'up-register-script'); ?></a>
            </div>

            <div id="scripts" class="tab-content active">
                <form method="post" action="options.php" enctype="multipart/form-data">
                    <?php settings_fields('up_register_script_settings'); ?>
                    
                    <table class="wp-list-table widefat fixed striped scripts-table">
                        <thead>
                            <tr>
                                <th width="30">&nbsp;</th>
                                <th>Handle</th>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Style/JS</th>
                                <th>Version</th>
                                <th>Dépendances</th>
                                <th>Position</th>
                                <th>Chargement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-rows">
                            <?php foreach ($scripts as $key => $script) : 
                                $is_file = isset($script['is_file']) && $script['is_file'];
                                $local_path = isset($script['local_path']) ? $script['local_path'] : '';
                                $type = isset($script['type']) ? $script['type'] : 'js';
                            ?>
                                <tr>
                                    <td class="drag-handle" title="Déplacer">⋮⋮</td>
                                    <td>
                                        <input type="text" 
                                               name="up_register_scripts[<?php echo esc_attr($key); ?>][handle]" 
                                               value="<?php echo esc_attr($script['handle']); ?>">
                                    </td>
                                    <td>
                                        <div class="script-source">
                                            <?php if (!empty($script['local_path']) && $script['is_file']): ?>
                                                <div class="source-url">
                                                    <strong>Local:</strong> 
                                                    <a href="<?php echo esc_url(site_url($script['local_path'])); ?>" target="_blank">
                                                        <?php echo esc_html(site_url($script['local_path'])); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($script['src'])): ?>
                                                <div class="source-url <?php echo (!empty($script['local_path']) ? 'original-url' : ''); ?>">
                                                    <?php if (!empty($script['local_path'])): ?>
                                                        <strong>Original:</strong> 
                                                    <?php endif; ?>
                                                    <input type="text" 
                                                           class="script-url" 
                                                           name="up_register_scripts[<?php echo esc_attr($key); ?>][src]" 
                                                           value="<?php echo esc_attr($script['src']); ?>" 
                                                           style="width: 100%;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" 
                                                   class="script-file hidden" 
                                                   name="script_file_<?php echo esc_attr($key); ?>" 
                                                   accept=".js,.css">
                                            <input type="hidden" 
                                                   name="up_register_scripts[<?php echo esc_attr($key); ?>][local_path]" 
                                                   value="<?php echo esc_attr($script['local_path']); ?>">
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
                                        <select name="up_register_scripts[<?php echo esc_attr($key); ?>][type]" class="script-type">
                                            <option value="js" <?php selected($type, 'js'); ?>>JavaScript</option>
                                            <option value="css" <?php selected($type, 'css'); ?>>CSS</option>
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
                                               value="<?php 
                                                   $deps = isset($script['deps']) ? $script['deps'] : array();
                                                   echo is_array($deps) ? esc_attr(implode(',', $deps)) : '';
                                               ?>"
                                               placeholder="jquery,gsap,...">
                                    </td>
                                    <td class="position-column">
                                        <?php if ($script['type'] === 'js'): ?>
                                            <label>
                                                <input type="checkbox" 
                                                       name="up_register_scripts[<?php echo esc_attr($key); ?>][in_footer]" 
                                                       value="1"
                                                       <?php checked(isset($script['in_footer']) ? $script['in_footer'] : true); ?>>
                                                Footer
                                            </label>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select name="up_register_scripts[<?php echo esc_attr($key); ?>][loading]">
                                            <option value="none" <?php selected($this->getLoadingType($script), 'none'); ?>>Ne pas charger</option>
                                            <option value="front" <?php selected($this->getLoadingType($script), 'front'); ?>>Front</option>
                                            <option value="admin" <?php selected($this->getLoadingType($script), 'admin'); ?>>Admin</option>
                                            <option value="both" <?php selected($this->getLoadingType($script), 'both'); ?>>Front & Admin</option>
                                            <option value="editor" <?php selected($this->getLoadingType($script), 'editor'); ?>>Éditeur</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="button delete-script">
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

            <div id="params" class="tab-content">
                <form method="post" action="options.php">
                    <?php 
                    settings_fields('up_register_script_params');
                    $options = get_option('up_register_script_params', [
                        'auto_load' => true,
                        'download_cdn' => false,
                        'local_path' => '/wp-content/uploads/scripts/'
                    ]);
                    ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Chargement automatique</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="up_register_script_params[auto_load]" 
                                           value="1" 
                                           <?php checked($options['auto_load']); ?>>
                                    Charger automatiquement les scripts selon leurs paramètres
                                </label>
                                <p class="description">
                                    Si désactivé, aucun script ne sera chargé automatiquement. 
                                    Vous devrez les charger manuellement via des fonctions PHP.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Téléchargement CDN</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="up_register_script_params[download_cdn]" 
                                           value="1" 
                                           <?php checked($options['download_cdn']); ?>>
                                    Télécharger automatiquement les fichiers CDN en local
                                </label>
                                <p class="description">
                                    Si activé, les scripts CDN seront téléchargés et servis depuis votre serveur.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Chemin local</th>
                            <td>
                                <input type="text" 
                                       name="up_register_script_params[local_path]" 
                                       value="<?php echo esc_attr($options['local_path']); ?>"
                                       class="regular-text">
                                <p class="description">
                                    Chemin où seront stockés les fichiers téléchargés (relatif à la racine WordPress)
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
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
        .position-column {
            text-align: center;
        }
        .position-column span {
            display: inline-block;
            line-height: 28px;
            color: #666;
        }
        .drag-handle {
            cursor: move;
            color: #999;
            text-align: center;
            vertical-align: middle;
        }
        .ui-sortable-helper {
            display: table;
            background: white !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        .ui-sortable-placeholder {
            visibility: visible !important;
            background: #f0f0f0 !important;
            height: 50px;
        }
        .script-source .source-url {
            margin-bottom: 5px;
        }
        .script-source .original-url {
            opacity: 0.7;
            font-size: 0.9em;
        }
        .script-source strong {
            display: inline-block;
            min-width: 60px;
            color: #666;
        }
        .source-url a {
            text-decoration: none;
        }
        .source-url a:hover {
            text-decoration: underline;
        }
        /* Styles généraux pour les inputs et selects */
        .scripts-table input[type="text"],
        .scripts-table select {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Styles spécifiques pour la source */
        .script-source .source-url {
            margin-bottom: 5px;
        }
        .script-source .original-url {
            opacity: 0.7;
            font-size: 0.9em;
        }
        .script-source strong {
            display: inline-block;
            min-width: 60px;
            color: #666;
        }
        .source-url a {
            text-decoration: none;
            word-break: break-all;
        }
        .source-url a:hover {
            text-decoration: underline;
        }

        /* Ajustements pour les colonnes spécifiques */
        .position-column {
            text-align: center;
        }
        .position-column span {
            display: inline-block;
            line-height: 28px;
            color: #666;
        }

        /* Ajustements pour les checkboxes */
        .load-options label {
            display: block;
            margin-bottom: 5px;
        }

        /* Exception pour les checkboxes qui ne doivent pas être à 100% */
        .scripts-table input[type="checkbox"] {
            width: auto;
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
                        <td class="drag-handle" title="Déplacer">⋮⋮</td>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][handle]" value=""></td>
                        <td>
                            <div class="script-source">
                                <input type="text" class="script-url" name="up_register_scripts[new_${timestamp}][src]" value="" style="width: 100%;">
                                <input type="file" class="script-file hidden" name="script_file_new_${timestamp}" accept=".js,.css">
                                <input type="hidden" name="up_register_scripts[new_${timestamp}][local_path]" value="">
                            </div>
                        </td>
                        <td>
                            <select name="up_register_scripts[new_${timestamp}][is_file]" class="source-type">
                                <option value="0">URL</option>
                                <option value="1">Fichier</option>
                            </select>
                        </td>
                        <td>
                            <select name="up_register_scripts[new_${timestamp}][type]" class="script-type">
                                <option value="js">JavaScript</option>
                                <option value="css">CSS</option>
                            </select>
                        </td>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][ver]" value=""></td>
                        <td><input type="text" name="up_register_scripts[new_${timestamp}][deps]" value="" placeholder="jquery,gsap,..."></td>
                        <td class="position-column">
                            <label>
                                <input type="checkbox" name="up_register_scripts[new_${timestamp}][in_footer]" value="1" checked>
                                Footer
                            </label>
                        </td>
                        <td>
                            <select name="up_register_scripts[new_${timestamp}][loading]">
                                <option value="none">Ne pas charger</option>
                                <option value="front">Front</option>
                                <option value="admin">Admin</option>
                                <option value="both">Front & Admin</option>
                                <option value="editor">Éditeur</option>
                            </select>
                        </td>
                        <td><button type="button" class="button delete-script">Supprimer</button></td>
                    </tr>
                `;
                $('.scripts-table tbody').append(newRow);
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

            $('.sortable-rows').sortable({
                handle: '.drag-handle',
                axis: 'y',
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    // Réindexer les noms des champs si nécessaire
                    $('.sortable-rows tr').each(function(index) {
                        $(this).find('input, select').each(function() {
                            var name = $(this).attr('name');
                            if (name) {
                                // Garder la clé originale mais mettre à jour l'ordre visuellement
                                $(this).closest('tr').attr('data-order', index);
                            }
                        });
                    });
                }
            });
        });
        </script>
        <?php
    }


    public function exportScriptsPhp() {
        check_ajax_referer('export_scripts');
        
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        $php = "<?php\n\n";
        
        // Fonction pour enregistrer tous les scripts/styles
        $php .= "function register_custom_assets() {\n";
        
        // Regrouper les assets par type
        $js_assets = array_filter($scripts, function($script) {
            return $script['type'] === 'js';
        });
        
        $css_assets = array_filter($scripts, function($script) {
            return $script['type'] === 'css';
        });
        
        // Enregistrer les styles
        if (!empty($css_assets)) {
            $php .= "\n    // Register Styles\n";
            foreach ($css_assets as $style) {
                $src = !empty($style['local_path']) && $style['is_file'] 
                    ? "site_url('" . esc_js($style['local_path']) . "')"
                    : "'" . esc_js($style['src']) . "'";
                
                $deps = implode("', '", $style['deps']);
                $deps = !empty($deps) ? "array('" . $deps . "')" : "array()";
                
                $php .= "    wp_register_style('{$style['handle']}', {$src}, {$deps}, '{$style['ver']}');\n";
            }
        }
        
        // Enregistrer les scripts
        if (!empty($js_assets)) {
            $php .= "\n    // Register Scripts\n";
            foreach ($js_assets as $script) {
                $src = !empty($script['local_path']) && $script['is_file'] 
                    ? "site_url('" . esc_js($script['local_path']) . "')"
                    : "'" . esc_js($script['src']) . "'";
                
                $deps = implode("', '", $script['deps']);
                $deps = !empty($deps) ? "array('" . $deps . "')" : "array()";
                
                $php .= "    wp_register_script('{$script['handle']}', {$src}, {$deps}, '{$script['ver']}', " . ($script['in_footer'] ? 'true' : 'false') . ");\n";
            }
        }
        
        $php .= "}\n";
        $php .= "add_action('init', 'register_custom_assets');\n\n";
        
        // Fonction pour le front-end
        $php .= "function enqueue_custom_front_assets() {\n";
        
        // Styles front-end
        $front_styles = array_filter($css_assets, function($style) {
            return !empty($style['load_front']);
        });
        if (!empty($front_styles)) {
            $php .= "\n    // Enqueue Styles\n";
            foreach ($front_styles as $style) {
                $php .= "    wp_enqueue_style('{$style['handle']}');\n";
            }
        }
        
        // Scripts front-end
        $front_scripts = array_filter($js_assets, function($script) {
            return !empty($script['load_front']);
        });
        if (!empty($front_scripts)) {
            $php .= "\n    // Enqueue Scripts\n";
            foreach ($front_scripts as $script) {
                $php .= "    wp_enqueue_script('{$script['handle']}');\n";
            }
        }
        
        $php .= "}\n";
        $php .= "add_action('wp_enqueue_scripts', 'enqueue_custom_front_assets');\n\n";
        
        // Fonction pour l'admin
        $php .= "function enqueue_custom_admin_assets() {\n";
        
        // Styles admin
        $admin_styles = array_filter($css_assets, function($style) {
            return !empty($style['load_admin']);
        });
        if (!empty($admin_styles)) {
            $php .= "\n    // Enqueue Styles\n";
            foreach ($admin_styles as $style) {
                $php .= "    wp_enqueue_style('{$style['handle']}');\n";
            }
        }
        
        // Scripts admin
        $admin_scripts = array_filter($js_assets, function($script) {
            return !empty($script['load_admin']);
        });
        if (!empty($admin_scripts)) {
            $php .= "\n    // Enqueue Scripts\n";
            foreach ($admin_scripts as $script) {
                $php .= "    wp_enqueue_script('{$script['handle']}');\n";
            }
        }
        
        $php .= "}\n";
        $php .= "add_action('admin_enqueue_scripts', 'enqueue_custom_admin_assets');\n\n";
        
        // Fonction pour l'éditeur
        $php .= "function enqueue_custom_editor_assets() {\n";
        
        // Styles éditeur
        $editor_styles = array_filter($css_assets, function($style) {
            return !empty($style['load_editor']);
        });
        if (!empty($editor_styles)) {
            $php .= "\n    // Enqueue Styles\n";
            foreach ($editor_styles as $style) {
                $php .= "    wp_enqueue_style('{$style['handle']}');\n";
            }
        }
        
        // Scripts éditeur
        $editor_scripts = array_filter($js_assets, function($script) {
            return !empty($script['load_editor']);
        });
        if (!empty($editor_scripts)) {
            $php .= "\n    // Enqueue Scripts\n";
            foreach ($editor_scripts as $script) {
                $php .= "    wp_enqueue_script('{$script['handle']}');\n";
            }
        }
        
        $php .= "}\n";
        $php .= "add_action('enqueue_block_editor_assets', 'enqueue_custom_editor_assets');\n";
        
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

    public function sanitizeScripts($input) {
        if (!is_array($input)) {
            return $this->default_scripts;
        }

        $sanitized = [];
        $options = get_option('up_register_script_params', [
            'auto_load' => true,
            'download_cdn' => false,
            'local_path' => '/wp-content/uploads/scripts/'
        ]);
        
        $should_download = !empty($options['download_cdn']);
        
        foreach ($input as $key => $script) {
            // Vérifier les champs requis
            if (empty($script['handle']) || (empty($script['src']) && empty($script['local_path']))) {
                continue;
            }

            // Gérer les dépendances
            $deps = isset($script['deps']) ? $script['deps'] : '';
            if (is_array($deps)) {
                $deps = implode(',', $deps);
            }
            
            $src = esc_url_raw($script['src']);
            $local_path_file = isset($script['local_path']) ? $script['local_path'] : '';
            $is_file = isset($script['is_file']) ? (bool)$script['is_file'] : false;
            
            // Télécharger le fichier si c'est une URL externe et que l'option est activée
            if ($should_download && !empty($src) && !$is_file && $this->isExternalUrl($src)) {
                $this->createScriptsDirectory($options['local_path']);
                $downloaded_file = $this->downloadAndSaveFile($src, $script['handle'], $script['type'], $options['local_path']);
                
                if ($downloaded_file) {
                    $local_path_file = $downloaded_file;
                    $is_file = true;
                }
            }

            // Gérer le nouveau type de chargement
            $loading = isset($script['loading']) ? $script['loading'] : 'none';
            
            $sanitized[$key] = [
                'handle' => sanitize_text_field($script['handle']),
                'src' => $src,
                'ver' => sanitize_text_field($script['ver']),
                'deps' => !empty($deps) ? array_map('sanitize_text_field', explode(',', $deps)) : [],
                'in_footer' => isset($script['in_footer']) ? true : false,
                'is_file' => $is_file,
                'type' => isset($script['type']) ? sanitize_text_field($script['type']) : 'js',
                'local_path' => $local_path_file,
                'load_front' => in_array($loading, ['front', 'both']),
                'load_admin' => in_array($loading, ['admin', 'both']),
                'load_editor' => $loading === 'editor'
            ];
        }

        return $sanitized;
    }

    private function downloadAndSaveFile($url, $handle, $type, $local_path) {
        // Vérifier que l'URL est valide
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Créer un nom de fichier unique basé sur le handle
        $extension = $type === 'js' ? '.js' : '.css';
        $filename = sanitize_file_name($handle . $extension);
        
        // Chemin complet du fichier
        $upload_path = untrailingslashit(ABSPATH) . $local_path;
        $file_path = $upload_path . $filename;
        
        // Télécharger le fichier
        $response = wp_safe_remote_get($url, [
            'timeout' => 60,
            'sslverify' => false
        ]);
        
        if (is_wp_error($response)) {
            error_log('Erreur de téléchargement : ' . $response->get_error_message());
            return false;
        }
        
        $content = wp_remote_retrieve_body($response);
        
        if (empty($content)) {
            error_log('Contenu vide pour : ' . $url);
            return false;
        }
        
        // Sauvegarder le fichier
        $saved = file_put_contents($file_path, $content);
        if ($saved === false) {
            error_log('Impossible de sauvegarder le fichier : ' . $file_path);
            return false;
        }
        
        // Retourner le chemin relatif
        return trailingslashit($local_path) . $filename;
    }

    private function isExternalUrl($url) {
        if (empty($url)) return false;
        
        $site_url = site_url();
        $parsed_url = parse_url($url);
        $parsed_site = parse_url($site_url);
        
        // Vérifier si c'est une URL complète
        if (isset($parsed_url['host'])) {
            return $parsed_url['host'] !== $parsed_site['host'];
        }
        
        // Pour les URLs relatives ou protocole-relatif
        return strpos($url, '//') === 0;
    }

    public function saveScriptsOrder() {
        check_ajax_referer('save_scripts_order');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $order = $_POST['order'];
        $scripts = get_option('up_register_scripts', $this->default_scripts);
        
        if (!empty($order) && is_array($order)) {
            // Créer un tableau temporaire pour le tri
            $sorted_scripts = [];
            asort($order); // Trier par valeur (index)
            
            // Réorganiser les scripts selon l'ordre
            foreach ($order as $key => $position) {
                if (isset($scripts[$key])) {
                    $sorted_scripts[$key] = $scripts[$key];
                }
            }
            
            // Mettre à jour l'option avec les scripts triés
            if (!empty($sorted_scripts)) {
                update_option('up_register_scripts', $sorted_scripts);
            }
        }
        
        wp_send_json_success();
    }

    private function getLoadingType($script) {
        if (!empty($script['load_front']) && !empty($script['load_admin'])) {
            return 'both';
        } elseif (!empty($script['load_front'])) {
            return 'front';
        } elseif (!empty($script['load_admin'])) {
            return 'admin';
        } elseif (!empty($script['load_editor'])) {
            return 'editor';
        }
        return 'none';
    }
}

// Initialisation
add_action('plugins_loaded', function() {
    UpRegisterScript::getInstance();
});
