<?php

/**
 * Define the internationalization functionality
 */
class WP_Control_Acceso_i18n {

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wp-control-acceso',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
