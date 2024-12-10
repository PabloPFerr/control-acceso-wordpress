<?php

/**
 * Fired during plugin activation
 */
class WP_Control_Acceso_Activator {

    /**
     * Crea el rol de auditor y configura las capacidades durante la activación
     */
    public static function activate() {
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

        // Crear tabla de registros si no existe
        self::create_tables();
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
