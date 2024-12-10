<?php

class WP_Control_Acceso {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = WP_CONTROL_ACCESO_VERSION;
        $this->plugin_name = 'wp-control-acceso';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->create_custom_post_types();
        $this->create_custom_tables();
        $this->define_shortcodes();
    }

    private function load_dependencies() {
        require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'includes/class-wp-control-acceso-loader.php';
        require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'includes/class-wp-control-acceso-i18n.php';
        require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'admin/class-wp-control-acceso-admin.php';
        require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'public/class-wp-control-acceso-public.php';

        $this->loader = new WP_Control_Acceso_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new WP_Control_Acceso_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new WP_Control_Acceso_Admin($this->get_plugin_name(), $this->get_version());

        // Agregar menú de administración
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Registrar estilos y scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Agregar enlaces de acción en la página de plugins
        $this->loader->add_filter('plugin_action_links_' . plugin_basename(WP_CONTROL_ACCESO_PLUGIN_DIR . 'wp-control-acceso.php'), 
            $plugin_admin, 'add_action_links');
    }

    private function define_public_hooks() {
        $plugin_public = new WP_Control_Acceso_Public($this->get_plugin_name(), $this->get_version());
        
        // Registrar estilos y scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Registrar shortcodes
        add_shortcode('control_acceso_registro', array($plugin_public, 'mostrar_registro_form'));
        add_shortcode('control_acceso_dashboard', array($plugin_public, 'mostrar_dashboard'));
        add_shortcode('control_acceso_reportes', array($plugin_public, 'mostrar_reportes'));
        
        // Registrar endpoints AJAX
        $this->loader->add_action('wp_ajax_wp_control_acceso_entrada', $plugin_public, 'registrar_entrada');
        $this->loader->add_action('wp_ajax_wp_control_acceso_salida', $plugin_public, 'registrar_salida');
        $this->loader->add_action('wp_ajax_wp_control_acceso_exportar', $plugin_public, 'exportar_registros');

        // Registrar el hook para el cierre automático
        $registros = new WP_Control_Acceso_Registros();
        $this->loader->add_action('wp_control_acceso_cierre_automatico', $registros, 'cerrar_registros_automaticamente');
    }

    private function create_custom_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = array();

        // Tabla de registros
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}control_acceso_registros (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            hora_entrada datetime NOT NULL,
            hora_salida datetime DEFAULT NULL,
            duracion float DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY hora_entrada (hora_entrada),
            KEY hora_salida (hora_salida)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function define_shortcodes() {
        add_shortcode('control_acceso_registro', array($this, 'render_registro_shortcode'));
        add_shortcode('control_acceso_dashboard', array($this, 'render_dashboard_shortcode'));
        add_shortcode('control_acceso_mis_reportes', array($this, 'render_mis_reportes_shortcode'));
    }

    /**
     * Verifica si el usuario actual tiene un permiso específico
     */
    public static function current_user_can($capability) {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        // Los administradores siempre tienen acceso completo
        if (in_array('administrator', $user->roles)) {
            return true;
        }

        // Los auditores solo pueden ver y exportar reportes
        if (in_array('control_acceso_auditor', $user->roles)) {
            return in_array($capability, ['control_acceso_view_reports', 'control_acceso_export_reports']);
        }

        // Para usuarios normales
        return current_user_can($capability);
    }

    /**
     * Renderiza el shortcode de registro
     */
    public function render_registro_shortcode() {
        if (!self::current_user_can('control_acceso_register_attendance')) {
            return '<div class="alert alert-warning">No tienes permiso para registrar asistencia.</div>';
        }
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/registro-form.php';
        return ob_get_clean();
    }

    /**
     * Renderiza el shortcode del dashboard
     */
    public function render_dashboard_shortcode() {
        if (!self::current_user_can('control_acceso_register_attendance')) {
            return '<div class="alert alert-warning">No tienes permiso para ver el dashboard.</div>';
        }
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Renderiza el shortcode de reportes personales
     */
    public function render_mis_reportes_shortcode() {
        if (!self::current_user_can('control_acceso_view_reports')) {
            return '<div class="alert alert-warning">No tienes permiso para ver reportes.</div>';
        }
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/mis-reportes.php';
        return ob_get_clean();
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
