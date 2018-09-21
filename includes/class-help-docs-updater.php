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
	 * The plugin slug.
	 *
	 * @since 0.4.1
	 * @var string
	 */
	private $slug;

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
			$instance       = new WSUWP_Help_Docs_Updater();
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
		add_filter( 'transient_update_plugins', array( $this, 'push_transient_update' ), 10, 1 );
		add_filter( 'site_transient_update_plugins', array( $this, 'push_transient_update' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'display_plugin_details' ), 10, 3 );
		add_filter( 'plugin_row_meta', array( $this, 'update_plugin_row_meta' ), 10, 2 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Sets the plugin GitHub credentials.
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
	 * Sets the plugin properties to pass to the GitHub API.
	 *
	 * @since 0.4.0
	 */
	public function set_properties() {
		if ( empty( $this->file ) ) {
			return false;
		}

		$this->plugin_meta = get_plugin_data( $this->file );
		$this->basename    = plugin_basename( $this->file );
		$this->slug        = current( explode( '/', $this->basename ) );
		$this->active      = is_plugin_active( $this->basename );
	}

	/**
	 * Gets the plugin repository info from the GitHub API.
	 *
	 * Connects to the plugin repository using the GitHub API (v3) to retrieve
	 * repo data in JSON format and parse it.
	 *
	 * @link https://developer.github.com/v3/
	 *
	 * @since 0.4.0
	 *
	 * @return array Array of parsed JSON GitHub repository details.
	 */
	private function get_repository_details() {
		// If a response already exists, return it.
		if ( ! empty( $this->github_response ) ) {
			return $this->github_response;
		}

		// Build the GitHub API request URI.
		$request_uri = sprintf( 'https://api.github.com/repos/%1$s/%2$s/releases',
			$this->username,
			$this->repository
		);

		// If there is an authorization token, append it to the request URI.
		if ( $this->auth_token ) {
			$request_uri = add_query_arg( 'access_token', $this->auth_token, $request_uri );
		}

		$response = wp_remote_get( esc_url_raw( $request_uri ) );

		if ( ! $response ) {
			return false;
		}

		// Get the response body and parse it.
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $api_response ) ) {
			$api_response = current( $api_response );
		}

		// If there is an auth token, add it to the zip url.
		if ( $this->auth_token ) {
			$api_response['zipball_url'] = add_query_arg( 'access_token', $this->auth_token, $api_response['zipball_url'] );
		}

		return $api_response;
	}

	/**
	 * Checks for a newer version and updates the transient if so.
	 *
	 * Callback function for the WP API `{site_}transient_update_plugins`
	 * filter hooks. Checks the version number from the installed version of the
	 * plugin against the latest release on GitHub and pushes the result into
	 * the WP plugin updates transient.
	 *
	 * @link https://codex.wordpress.org/Transients_API
	 *
	 * @since 0.4.0
	 *
	 * @param object $transient Required. The WP update_plugins transient.
	 * @return object $transient The same or modified transient object.
	 */
	public function push_transient_update( $transient ) {
		// Return early if WP is installing.
		if ( wp_installing() ) {
			return $transient;
		}

		// Return early if the WordPress hasn't checked for updates.
		if ( empty( $transient->checked[ $this->basename ] ) ) {
			return $transient;
		}

		// Try to get plugin details from the cache before checking the API.
		if ( false === ( $this->github_response = get_transient( 'update_plugin_' . WSUWP_Help_Docs::$post_type_slug ) ) ) {
			$this->github_response = $this->get_repository_details();

echo '<script type="text/javascript">console.log("Step 1: No cached version found; ran a new request.")</script>'; // DEBUG: check if this fired

			if ( ! is_wp_error( $this->github_response ) && ! empty( $this->github_response['zipball_url'] ) ) {
				// Save results of a successful API call to a 12-hour transient cache.
				set_transient( 'update_plugin_' . WSUWP_Help_Docs::$post_type_slug, $this->github_response, 43200 );

				dbgx_trace_var( get_transient( 'update_plugin_' . WSUWP_Help_Docs::$post_type_slug ) ); // DEBUG:
			}
		}

		// If have the plugin details, add them to the update_plugins transient.
		if ( $this->github_response ) {

			echo '<script type="text/javascript">console.log("Yes there is a response.")</script>'; // DEBUG: check if this fired
			dbgx_trace_var( $this->basename ); // DEBUG: why are you missing?

			// If GitHub version greater than installed version.
			if ( true === version_compare( str_replace( 'v', '', $this->github_response['tag_name'] ), $transient->checked[ $this->basename ], 'gt' ) ) {

				echo '<script type="text/javascript">console.log("Yes version compare was true.")</script>'; // DEBUG: check if this fired

				$package = $this->github_response['zipball_url'];

				$obj              = new stdClass();
				$obj->slug        = $this->slug;
				$obj->plugin      = $this->basename;
				$obj->new_version = $this->github_response['tag_name'];
				$obj->package     = $package;
				$obj->url         = $this->plugin_meta["PluginURI"];

				$transient->response[ $this->basename ] = $obj;
			}

			echo '<script type="text/javascript">console.log("Trigger 2")</script>'; // DEBUG: check if this fired
			if ( isset( $obj ) ) {
				dbgx_trace_var( $obj ); // DEBUG: check the response format
			}
			// $obj = stdClass::__set_state(array(
			//    'slug' => '',
			//    'plugin' => NULL,
			//    'new_version' => 'v0.3.1',
			//    'package' => 'https://api.github.com/repos/washingtonstateuniversity/WSUWP-Plugin-Help-Docs/zipball/v0.3.1',
			//    'url' => NULL,
			// ))
		}

		echo '<script type="text/javascript">console.log("Trigger 3")</script>'; // DEBUG: check if this fired
		dbgx_trace_var( $transient ); // DEBUG: check the response format

		return $transient;
	}

	/**
	 * display the plugin details modal window
	 *
	 * retrieve and display the plugin release info as the details window.
	 */
	public function display_plugin_details( $result, $action, $args ) {
		// Do nothing if this isn't a request for information.
		if ( 'plugin_information' !== $action ) {
			return false;
		}

		// Return the result now if it isn't our plugin.
		if ( $this->slug !== $args->slug ) {
			return $result;
		}

		// Try to get plugin details from the cache before checking the API.
		if ( false === ( $this->github_response = get_transient( 'update_plugin_' . WSUWP_Help_Docs::$post_type_slug ) ) ) {
			$this->github_response = $this->get_repository_details();

			if ( ! is_wp_error( $this->github_response ) && ! empty( $this->github_response['zipball_url'] ) ) {
				// Save results of a successful API call to a 12-hour transient cache.
				set_transient( 'update_plugin_' . WSUWP_Help_Docs::$post_type_slug, $this->github_response, 43200 );
			}
		}

		if ( $this->github_response ) {

			$result = new stdClass();
			$result->name = $this->plugin_meta["Name"];
			$result->slug = $this->slug;
			$result->version = str_replace( 'v', '', $this->github_response['tag_name'] );
			$result->requires = '3.3';
			$result->tested = '4.9';
			$result->rating = '100';
			$result->num_ratings = '1';
			$result->downloaded = '1';
			$result->author = $this->plugin_meta["AuthorName"];
			$result->author_profile = $this->plugin_meta["AuthorURI"];
			$result->last_updated = $this->github_response['published_at'];
			$result->homepage = $this->plugin_meta["PluginURI"];
			$result->short_description = $this->plugin_meta["Description"];
			$result->sections = array(
				'description' => $this->plugin_meta["Description"],
				'updates' => $this->github_response['body'],
			);
			$result->download_link = $this->github_response['zipball_url'];

			return $result;
		}

		return false;
	}

	/**
	 * Adds a "view details" link to the plugin row meta.
	 *
	 * Callback function for the `plugin_row_meta` filter hook. This funciton
	 * modifies the plugin_meta variable to add a "View Details" link like the
	 * one for plugins in the WP plugin repository. The link will generate
	 * the modal ({@uses install_plugin_information()}) using `plugins_api()`,
	 * which we filter with `$this->display_plugin_details()`.
	 *
	 * @since 0.4.1
	 *
	 * @param array @plugin_meta The plugin's metadata.
	 * @param string @plugin_file Path to the plugin file, relative to the plugins directory.
	 * @return string HTML formatted meta data for the plugins table row, altered or not.
	 */
	public function update_plugin_row_meta( $plugin_meta, $plugin_file ) {
		// Return the result now if it isn't our plugin.
		if ( $this->basename !== $plugin_file ) {
			return $plugin_meta;
		}

		if ( current_user_can( 'install_plugins' ) ) {
			$plugin_meta[] = sprintf( '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
				esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&TB_iframe=true&width=600&height=550' ) ),
				esc_attr( sprintf( __( 'More information about %s', 'wsuwp-help-docs' ), $this->plugin_meta['Name'] ) ),
				esc_attr( $this->plugin_meta['Name'] ),
				__( 'View details', 'wsuwp-help-docs' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * actions for after installation
	 */
	public function after_install( $response, $hook_extra, $result ) {
		// steps to take after installation
	}
}
