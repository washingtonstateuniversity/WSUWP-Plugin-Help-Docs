<?php
/**
 * WSUWP Help Docs Updater: WSUWP_Help_Docs_Updater class
 *
 * @package WSUWP_Help_Docs
 * @since 0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The WSUWP Help Docs updater class.
 *
 * Checks the WSUWP Help Docs GitHub repository latest release version against
 * the current installed version to alert administrators of newer versions.
 * Allows administrators to update the plugin automatically from the plugins
 * screen.
 *
 * @since 0.4.0
 */
class WSUWP_Help_Docs_Updater {
	/**
	 * The main plugin file path.
	 *
	 * @since 0.4.0
	 * @var string
	 */
	private $file;

	/**
	 * The plugin metadata.
	 *
	 * @since 0.4.0
	 * @var array
	 */
	private $plugin_meta;

	/**
	 * Path to the plugin file or directory.
	 *
	 * @since 0.4.0
	 * @var string
	 */
	private $basename;

	/**
	 * Whether the plugin is activated.
	 *
	 * @since 0.4.0
	 * @var bool
	 */
	private $active;

	/**
	 * The username for the plugin GitHub repository.
	 *
	 * @since 0.4.0
	 * @var string
	 */
	private $username;

	/**
	 * The name of the plugin GitHub repository.
	 *
	 * @since 0.4.0
	 * @var string
	 */
	private $repository;

	/**
	 * The API authorization token for private GitHub repositories.
	 *
	 * @since 0.4.0
	 * @var string
	 */
	private $auth_token;

	/**
	 * The GitHub API response data.
	 *
	 * @since 0.4.0
	 * @var array
	 */
	private $github_response;


	/**
	 * Instantiates WSUWP Help singleton.
	 *
	 * @since 0.1.0
	 *
	 * @return object The WSUWP Help object
	 */
	public static function get_instance( $file = '' ) {
		static $instance = null;

		// Only set up and activate the plugin if it hasn't already been done.
		if ( null === $instance ) {
			$instance = new WSUWP_Help_Docs_Updater();
			$instance->file = $file;
			$instance->setup_hooks();
		}

		return $instance;
	}

	/**
	 * An empty constructor to prevent WSUWP Help being loaded more than once.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		/* Nothing doing. */
	}

	/**
	 * Loads the WP API actions and hooks.
	 *
	 * @since 0.4.0
	 *
	 * @access private
	 */
	private function setup_hooks() {
		add_action( 'admin_init', array( $this, 'set_properties' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'display_plugin_details' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Defines the plugin GitHub credentials.
	 *
	 * @since 0.4.0
	 */
	public function set_github_credentials( $props ) {
		$defaults = array(
			'username'   => $this->username,
			'repository' => $this->repository,
			'auth_token' => $this->auth_token,
		);

		$props = wp_parse_args( $props, $defaults );

		$this->username   = $props['username'];
		$this->repository = $props['repository'];
		$this->auth_token = $props['auth_token'];
	}

	/**
	 * Defines the plugin properties to pass to the GitHub API.
	 *
	 * @since 0.4.0
	 */
	public function set_properties() {
		if ( empty( $this->file ) ) {
			return false;
		}

		$this->plugin   = get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active   = is_plugin_active( $this->basename );
	}

	/**
	 * Gets the plugin repository info from the GitHub API.
	 *
	 * @link
	 *
	 * @since 0.4.0
	 */

	/**
	 * update the transient etc
	 */
	public function modify_transient( $transient ) {
		// check and update the transient
	}

	/**
	 * display the plugin details modal window
	 */
	public function display_plugin_details( $result, $action, $args ) {
		// retrieve and display the plugin release info as the details window.
	}

	/**
	 * actions for after installation
	 */
	public function after_install( $response, $hook_extra, $result ) {
		// steps to take after installation
	}
}
