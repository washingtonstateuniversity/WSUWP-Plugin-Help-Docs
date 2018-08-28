<?php
/*
Plugin Name: WSUWP HRS Help
Version: 0.1.0
Description: A plugin to create a Help custom post type for use in the Admin area.
Author: Adam Turner, washingtonstateuniversity
Author URI: https://hrs.wsu.edu/
Plugin URI: https://github.com/washingtonstateuniversity/wsuwp-hrs-help
Text Domain: wsuwp-hrs-help
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
require_once __DIR__ . '/includes/class-hrs-help-setup.php';

// Flush rules on activation and clean up on deactivation.
register_activation_hook( __FILE__, array( 'WSU_HRS_Help', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WSU_HRS_Help', 'deactivate' ) );
