<?php
/*
Plugin Name: WSUWP Help Docs
Version: 0.2.1
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

// Flush rules on activation and clean up on deactivation.
register_activation_hook( __FILE__, array( 'WSUWP_Help_Docs', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WSUWP_Help_Docs', 'deactivate' ) );
