<?php
/*
Plugin Name: WSUWP Help Docs
Version: 0.2.2
Description: A plugin to create Help documents for use in the Admin area.
Author: Adam Turner, washingtonstateuniversity
Author URI: https://github.com/washingtonstateuniversity/
Plugin URI: https://github.com/washingtonstateuniversity/wsuwp-plugin-help-docs
Text Domain: wsuwp-help-docs
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Loads the core plugin class.
 *
 * @since 0.1.0
 */
require_once __DIR__ . '/includes/class-help-docs-setup.php';

// Starts things up.
add_action( 'after_setup_theme', 'load_wsuwp_help' );

// Flushes rules on activation and cleans up on deactivation.
register_activation_hook( __FILE__, array( 'WSUWP_Help_Docs', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WSUWP_Help_Docs', 'deactivate' ) );

/**
 * Creates an instance of the WSUWP Help class.
 *
 * @since 0.1.0
 *
 * @return object An instance of WSUWP_Help_Docs
 */
function load_wsuwp_help() {
	return WSUWP_Help_Docs::get_instance();
}
