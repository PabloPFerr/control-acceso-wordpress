<?php

/**
 * Fired during plugin activation
 */
class WP_Control_Acceso_Activator {

    /**
     * Crea el rol de auditor y configura las capacidades durante la activación
     */
    public static function activate() {
        global $wpdb;
        $tabla_registros = $wpdb->prefix . 'control_acceso_registros';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $tabla_registros (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            hora_entrada datetime NOT NULL,
            hora_salida datetime DEFAULT NULL,
            duracion float DEFAULT NULL,
            cierre_automatico tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Programar el evento cron si no existe
        if (!wp_next_scheduled('wp_control_acceso_cierre_automatico')) {
            // Programar para la próxima medianoche
            $tomorrow = strtotime('tomorrow midnight');
            wp_schedule_event($tomorrow, 'daily', 'wp_control_acceso_cierre_automatico');
        }

        // Crear rol de auditor si no existe
        if (!get_role('control_acceso_auditor')) {
            add_role(
                'control_acceso_auditor',
                'Auditor de Control de Acceso',
                array(
                    'read' => true,
                    'control_acceso_view_reports' => true,
                    'control_acceso_export_reports' => true
                )
            );
        }

        // Asegurar que el rol de administrador tenga todas las capacidades
        $admin = get_role('administrator');
        $admin->add_cap('control_acceso_manage_all');
        $admin->add_cap('control_acceso_view_reports');
        $admin->add_cap('control_acceso_export_reports');
        $admin->add_cap('control_acceso_register_attendance');
        $admin->add_cap('control_acceso_manage_users');

        // Capacidades para usuarios normales
        $subscriber = get_role('subscriber');
        if ($subscriber) {
            $subscriber->add_cap('control_acceso_register_attendance');
        }
    }

    /**
     * Crea las tablas necesarias en la base de datos
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}control_acceso_registros (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            hora_entrada datetime NOT NULL,
            hora_salida datetime DEFAULT NULL,
            duracion float DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
