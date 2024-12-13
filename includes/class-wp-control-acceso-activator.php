<?php

/**
 * Fired during plugin activation
 */
class WP_Control_Acceso_Activator {

    /**
     * Crea los roles y configura las capacidades durante la activación
     */
    public static function activate() {
        self::create_tables();
        self::setup_roles_and_capabilities();
        self::setup_cron_jobs();
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
            cierre_automatico tinyint(1) DEFAULT 0,
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

    /**
     * Configura los roles y capacidades
     */
    private static function setup_roles_and_capabilities() {
        // Crear rol de empleado si no existe
        if (!get_role('empleado')) {
            add_role(
                'empleado',
                'Empleado',
                array(
                    'read' => true,
                    'control_acceso_register_attendance' => true, // Puede registrar asistencia
                    'control_acceso_view_own_reports' => true    // Puede ver sus propios reportes
                )
            );
        }

        // Crear rol de supervisor si no existe
        if (!get_role('supervisor_acceso')) {
            add_role(
                'supervisor_acceso',
                'Supervisor de Control de Acceso',
                array(
                    'read' => true,
                    'control_acceso_view_reports' => true,      // Puede ver todos los reportes
                    'control_acceso_export_reports' => true,    // Puede exportar reportes
                    'control_acceso_manage_users' => true       // Puede gestionar empleados
                )
            );
        }

        // Crear rol de auditor si no existe
        if (!get_role('auditor_acceso')) {
            add_role(
                'auditor_acceso',
                'Auditor de Control de Acceso',
                array(
                    'read' => true,
                    'control_acceso_view_reports' => true,      // Solo puede ver reportes
                    'control_acceso_export_reports' => true     // Y exportarlos
                )
            );
        }

        // Asegurar que el rol de administrador tenga todas las capacidades
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('control_acceso_manage_all');           // Gestión completa
            $admin->add_cap('control_acceso_view_reports');         // Ver reportes
            $admin->add_cap('control_acceso_export_reports');       // Exportar reportes
            $admin->add_cap('control_acceso_register_attendance');  // Registrar asistencia
            $admin->add_cap('control_acceso_manage_users');         // Gestionar usuarios
        }
    }

    /**
     * Configura los trabajos cron
     */
    private static function setup_cron_jobs() {
        // Programar el evento cron si no existe
        if (!wp_next_scheduled('wp_control_acceso_cierre_automatico')) {
            // Programar para la próxima medianoche
            $tomorrow = strtotime('tomorrow midnight');
            wp_schedule_event($tomorrow, 'daily', 'wp_control_acceso_cierre_automatico');
        }
    }
}
