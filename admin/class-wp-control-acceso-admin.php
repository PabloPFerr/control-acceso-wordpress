<?php

class WP_Control_Acceso_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Agregar hooks para el manejo de roles en el perfil de usuario
        add_action('show_user_profile', array($this, 'add_control_acceso_role_field'));
        add_action('edit_user_profile', array($this, 'add_control_acceso_role_field'));
        add_action('personal_options_update', array($this, 'save_control_acceso_role'));
        add_action('edit_user_profile_update', array($this, 'save_control_acceso_role'));
        add_action('wp_ajax_get_control_acceso_role', array($this, 'get_control_acceso_role_ajax'));
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, 
            plugin_dir_url(__FILE__) . 'css/wp-control-acceso-admin.css', 
            array(), $this->version, 'all');
        
        // Bootstrap y Font Awesome
        wp_enqueue_style('bootstrap', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('fontawesome', 
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/wp-control-acceso-admin.js',
            array('jquery'), $this->version, false);

        // Bootstrap JS
        wp_enqueue_script('bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'), '5.3.0', true);

        // Localizar script para AJAX
        wp_localize_script($this->plugin_name, 'wpControlAcceso', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_control_acceso_nonce')
        ));
    }

    public function add_menu() {
        // Menú principal
        add_menu_page(
            'Control de Acceso',
            'Control de Acceso',
            'read',
            $this->plugin_name,
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-clock',
            26
        );

        // Submenús
        if (current_user_can('control_acceso_manage_users')) {
            add_submenu_page(
                $this->plugin_name,
                'Usuarios',
                'Usuarios',
                'control_acceso_manage_users',
                $this->plugin_name . '-users',
                array($this, 'display_plugin_users_page')
            );
        }

        if (current_user_can('control_acceso_view_reports')) {
            add_submenu_page(
                $this->plugin_name,
                'Reportes',
                'Reportes',
                'control_acceso_view_reports',
                $this->plugin_name . '-reports',
                array($this, 'display_plugin_reports_page')
            );
        }

        if (current_user_can('control_acceso_manage_all')) {
            add_submenu_page(
                $this->plugin_name,
                'Configuración',
                'Configuración',
                'control_acceso_manage_all',
                $this->plugin_name . '-settings',
                array($this, 'display_plugin_admin_settings')
            );
        }

        // Menú de ayuda (accesible para todos)
        add_submenu_page(
            $this->plugin_name,
            'Ayuda',
            'Ayuda',
            'read',
            $this->plugin_name . '-ayuda',
            array($this, 'display_ayuda_page')
        );
    }

    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . 
            __('Dashboard', 'wp-control-acceso') . '</a>',
        );
        return array_merge($settings_link, $links);
    }

    /**
     * Muestra el dashboard administrativo
     */
    public function display_plugin_admin_dashboard() {
        if (current_user_can('control_acceso_view_reports')) {
            include_once 'partials/wp-control-acceso-admin-dashboard.php';
        } else {
            echo '<div class="wrap"><div class="alert alert-warning">No tienes permiso para ver esta página.</div></div>';
        }
    }

    /**
     * Muestra la página de reportes
     */
    public function display_plugin_reports_page() {
        if (current_user_can('control_acceso_view_reports')) {
            include_once 'partials/wp-control-acceso-admin-reportes.php';
        } else {
            echo '<div class="wrap"><div class="alert alert-warning">No tienes permiso para ver esta página.</div></div>';
        }
    }

    /**
     * Muestra la página de ayuda
     */
    public function display_ayuda_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-control-acceso-admin-ayuda.php';
    }

    /**
     * Agrega el campo de rol en el perfil de usuario
     */
    public function add_control_acceso_role_field($user) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $control_acceso_role = get_user_meta($user->ID, 'control_acceso_role', true);
        ?>
        <h3>Control de Acceso</h3>
        <table class="form-table">
            <tr>
                <th><label for="control_acceso_role">Rol en Control de Acceso</label></th>
                <td>
                    <select name="control_acceso_role" id="control_acceso_role">
                        <option value="">-- Seleccionar Rol --</option>
                        <option value="control_acceso_user" <?php selected($control_acceso_role, 'control_acceso_user'); ?>>
                            Usuario Normal
                        </option>
                        <option value="control_acceso_auditor" <?php selected($control_acceso_role, 'control_acceso_auditor'); ?>>
                            Auditor
                        </option>
                    </select>
                    <p class="description">Selecciona el rol del usuario para el plugin Control de Acceso.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Guarda el rol de Control de Acceso
     */
    public function save_control_acceso_role($user_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (isset($_POST['control_acceso_role'])) {
            $old_role = get_user_meta($user_id, 'control_acceso_role', true);
            $new_role = sanitize_text_field($_POST['control_acceso_role']);

            // Remover el rol anterior
            if ($old_role) {
                $user = new WP_User($user_id);
                $user->remove_role($old_role);
            }

            // Asignar el nuevo rol
            if ($new_role) {
                $user = new WP_User($user_id);
                $user->add_role($new_role);
            }

            update_user_meta($user_id, 'control_acceso_role', $new_role);
        }
    }

    /**
     * Obtiene el rol de Control de Acceso vía AJAX
     */
    public function get_control_acceso_role_ajax() {
        check_ajax_referer($this->plugin_name, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permiso denegado');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$user_id) {
            wp_send_json_error('Usuario no válido');
        }

        $role = get_user_meta($user_id, 'control_acceso_role', true);
        wp_send_json_success($role);
    }
}
