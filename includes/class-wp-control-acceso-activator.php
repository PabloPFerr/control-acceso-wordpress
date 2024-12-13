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
        // Remover el rol existente si existe
        remove_role('empleado');
        
        // Crear el rol de empleado con las capacidades básicas de WordPress
        add_role(
            'empleado',
            'Empleado',
            array(
                'read' => true,
                'control_acceso_register_attendance' => true,
                'control_acceso_view_own_reports' => true,
                'control_acceso_view_reports' => false, // Solo para administradores
            )
        );

        // Asignar capacidades al rol de administrador
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('control_acceso_register_attendance');
            $admin->add_cap('control_acceso_view_own_reports');
            $admin->add_cap('control_acceso_view_reports');
        }

        // Debug: Verificar que el rol se creó correctamente
        $empleado = get_role('empleado');
        if ($empleado) {
            error_log('Rol de empleado creado/actualizado con capacidades: ' . print_r($empleado->capabilities, true));
        } else {
            error_log('Error: No se pudo crear el rol de empleado');
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
