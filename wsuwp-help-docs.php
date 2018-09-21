<?php
/*
Plugin Name: WSUWP Help Docs
Version: 0.3.1
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

/**
 * Creates an instance of the WSUWP Help Docs Updater class.
 *
 * This class handles updating the plugin from its GitHub repository. Update
 * the GitHub username and repository name here to match the desired build
 * source.
 *
 * @since 0.4.0
 *
 * @return object Instance of WSUWP_Help_Docs_Updater
 */
function load_wsuwp_help_updater() {
	$updater = WSUWP_Help_Docs_Updater::get_instance( __FILE__ );

	/*
	 * Define the plugin repo GitHub credentials. Required properties include:
	 * 'username' (the GitHub username) and 'repository' (the name of the repo).
	 * For private repositories you must also include an auth token value for
	 * the 'auth_token' property.
	 */
	$updater->set_github_credentials( array(
		'username'   => 'washingtonstateuniversity',
		'repository' => 'WSUWP-Plugin-Help-Docs',
	) );

	return $updater;
}
