<?php
/**
 * Plugin Name: Control de Acceso
 * Plugin URI: https://tudominio.com/plugins/control-acceso
 * Description: Sistema de control de acceso y registro de asistencia para empleados
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tudominio.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-control-acceso
 * Domain Path: /languages
 */

// Si este archivo es llamado directamente, abortar
if (!defined('WPINC')) {
    die;
}

// Definir constantes del plugin
define('WP_CONTROL_ACCESO_VERSION', '1.0.0');
define('WP_CONTROL_ACCESO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_CONTROL_ACCESO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Activación y desactivación del plugin
register_activation_hook(__FILE__, 'activate_wp_control_acceso');
register_deactivation_hook(__FILE__, 'deactivate_wp_control_acceso');

/**
 * Código que se ejecuta durante la activación del plugin
 */
function activate_wp_control_acceso() {
    require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'includes/class-wp-control-acceso-activator.php';
    WP_Control_Acceso_Activator::activate();
}

/**
 * Código que se ejecuta durante la desactivación del plugin
 */
function deactivate_wp_control_acceso() {
    require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'includes/class-wp-control-acceso-deactivator.php';
    WP_Control_Acceso_Deactivator::deactivate();
}

// Cargar la clase principal del plugin
require WP_CONTROL_ACCESO_PLUGIN_DIR . 'includes/class-wp-control-acceso.php';

/**
 * Comienza la ejecución del plugin
 */
function run_wp_control_acceso() {
    $plugin = new WP_Control_Acceso();
    $plugin->run();
}

run_wp_control_acceso();
