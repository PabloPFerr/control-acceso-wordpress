<?php

class WP_Control_Acceso_Public {
    private $plugin_name;
    private $version;
    private $registros;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->registros = new WP_Control_Acceso_Registros();
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/wp-control-acceso-public.css',
            array(), $this->version, 'all');
        
        wp_enqueue_style('bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/wp-control-acceso-public.js',
            array('jquery'), $this->version, false);

        wp_enqueue_script('bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'), '5.3.0', true);

        wp_localize_script($this->plugin_name, 'wpControlAcceso', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_control_acceso_nonce')
        ));
    }

    /**
     * Shortcode para el formulario de registro
     */
    public function display_registro_form() {
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">Debes iniciar sesión para registrar tu asistencia.</div>';
        }

        // Verificar si el usuario tiene el rol correcto
        $user = wp_get_current_user();
        if (!in_array('empleado', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            return '<div class="alert alert-warning">No tienes permisos para registrar asistencia.</div>';
        }

        $user_id = get_current_user_id();
        $registro_activo = $this->tiene_registro_activo($user_id);

        ob_start();
        include 'partials/registro-form.php';
        return ob_get_clean();
    }

    /**
     * Shortcode para el dashboard personal
     */
    public function display_dashboard() {
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">Debes iniciar sesión para ver tu dashboard.</div>';
        }

        // Verificar si el usuario tiene el rol correcto
        $user = wp_get_current_user();
        if (!in_array('empleado', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            return '<div class="alert alert-warning">No tienes permisos para ver el dashboard.</div>';
        }

        $user_id = get_current_user_id();
        $fecha_inicio = date('Y-m-d');
        $fecha_fin = date('Y-m-d');

        $registros_hoy = $this->registros->get_registros_por_fecha($fecha_inicio, $fecha_fin, $user_id);
        $horas_hoy = $this->registros->get_horas_por_dia($fecha_inicio, $fecha_fin, $user_id);
        $registro_activo = $this->tiene_registro_activo($user_id);

        ob_start();
        include 'partials/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Verificar si el usuario tiene un registro activo
     */
    private function tiene_registro_activo($user_id) {
        global $wpdb;
        
        // Obtener el nombre correcto de la tabla
        $table_name = $wpdb->prefix . 'control_acceso_registros';
        
        // Construir la consulta
        $query = $wpdb->prepare("
            SELECT * FROM {$table_name}
            WHERE user_id = %d 
            AND hora_salida IS NULL
            AND DATE(hora_entrada) = CURDATE()
            ORDER BY hora_entrada DESC
            LIMIT 1
        ", $user_id);
        
        // Ejecutar la consulta
        $registro = $wpdb->get_row($query);
        
        // Si hay un error en la consulta, registrarlo
        if ($wpdb->last_error) {
            error_log('Error en tiene_registro_activo: ' . $wpdb->last_error);
            error_log('Query ejecutada: ' . $query);
            return null;
        }
        
        return $registro;
    }

    /**
     * Manejar registro de entrada vía AJAX
     */
    public function handle_registro_entrada() {
        check_ajax_referer('wp_control_acceso_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('No has iniciado sesión');
        }

        // Verificar si el usuario tiene el rol correcto
        $user = wp_get_current_user();
        if (!in_array('empleado', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            wp_send_json_error('No tienes permisos para registrar asistencia');
        }

        $user_id = get_current_user_id();
        $resultado = $this->registros->registrar_entrada($user_id);

        if (is_wp_error($resultado)) {
            wp_send_json_error($resultado->get_error_message());
        }

        wp_send_json_success('Entrada registrada correctamente');
    }

    /**
     * Manejar registro de salida vía AJAX
     */
    public function handle_registro_salida() {
        check_ajax_referer('wp_control_acceso_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('No has iniciado sesión');
        }

        // Verificar si el usuario tiene el rol correcto
        $user = wp_get_current_user();
        if (!in_array('empleado', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            wp_send_json_error('No tienes permisos para registrar asistencia');
        }

        $user_id = get_current_user_id();
        $resultado = $this->registros->registrar_salida($user_id);

        if (is_wp_error($resultado)) {
            wp_send_json_error($resultado->get_error_message());
        }

        wp_send_json_success('Salida registrada correctamente');
    }
}
