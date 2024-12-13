<?php

/**
 * Fired during plugin deactivation
 */
class WP_Control_Acceso_Deactivator {

    public static function deactivate() {
        // Eliminar el evento cron programado
        $timestamp = wp_next_scheduled('wp_control_acceso_cierre_automatico');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wp_control_acceso_cierre_automatico');
        }

        // No eliminamos las tablas ni los roles para mantener los datos
        // Si se desea eliminar todo, descomentar las siguientes líneas
        
        /*
        // Eliminar rol de auditor
        remove_role('control_acceso_auditor');

        // Eliminar capacidades del administrador
        $admin = get_role('administrator');
        if ($admin) {
            $admin->remove_cap('control_acceso_view_reports');
            $admin->remove_cap('control_acceso_export_reports');
            $admin->remove_cap('control_acceso_manage_settings');
        }
        */
    }
}
