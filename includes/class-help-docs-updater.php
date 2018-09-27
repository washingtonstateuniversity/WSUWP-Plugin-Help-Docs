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
	 * The plugin slug.
	 *
	 * @since 0.4.1
	 * @since 0.5.0 Changed from private to public static.
	 * @var string
	 */
	public static $slug;

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
	 * @since 0.4.0
	 *
	 * @return object The WSUWP_Help_Docs_Updater object
	 */
	public static function get_instance( $file ) {
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
	 * @since 0.4.0
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
		add_filter( 'upgrader_post_install', array( $this, 'update_post_install' ), 10, 3 );
		add_filter( 'extra_plugin_headers', array( $this, 'add_plugin_headers' ), 10, 1 );
	}

	/**
	 * Sets the plugin GitHub credentials.
	 *
	 * @since 0.4.0
	 *
	 * @param $props {
	 *     @type string $username   Required. A valid GitHub username for the repository.
	 *     @type string $repository Required. The GitHub repository to watch for updates.
	 *     @type string $auth_token Optional. A GitHub authorization token.
	 * }
	 * @return void
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
	 * Sets the plugin properties.
	 *
	 * @since 0.4.0
	 */
	public function set_properties() {
		if ( empty( $this->file ) ) {
			return false;
		}

		$this->plugin_meta = get_plugin_data( $this->file );
		$this->basename    = plugin_basename( $this->file );
		self::$slug        = current( explode( '/', $this->basename ) );
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
	 * @return array|false Array of parsed JSON GitHub repository details or false if the request failed.
	 */
	private function get_repository_details() {
		// If a response already exists, return it.
		if ( ! empty( $this->github_response ) ) {
			return $this->github_response;
		}

		// Build the GitHub API request URI.
		$request_uri = sprintf( 'https://api.github.com/repos/%1$s/%2$s/releases/latest',
			$this->username,
			$this->repository
		);

		// If there is an authorization token, append it to the request URI.
		if ( $this->auth_token ) {
			$request_uri = add_query_arg( 'access_token', $this->auth_token, $request_uri );
		}

		$raw_response = wp_remote_get( esc_url_raw( $request_uri ) );

		if ( is_wp_error( $raw_response ) ) {
			$this->error( $raw_response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $raw_response );

		if ( 200 !== (int) $response_code ) {
			$this->error( sprintf( 'GitHub API request failed. The request for <%1$s> returned HTTP code: %2$s',
				esc_url_raw( $request_uri ),
				$response_code
			) );
			return false;
		}

		// Get the response body and parse it.
		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		// If there is an auth token, add it to the zip url.
		if ( $this->auth_token ) {
			$response['zipball_url'] = add_query_arg( 'access_token', $this->auth_token, $response['zipball_url'] );
		}

		return $response;
	}

	/**
	 * Deletes the `update_plugin_{plugin-slug}` transient to clear the cache.
	 *
	 * @since 0.5.0
	 *
	 * @return bool True if successful, false otherwise or if transient doesn't exist.
	 */
	public static function flush_transient_cache() {
		if ( get_transient( 'update_plugin_' . self::$slug ) ) {
			$deleted = delete_transient( 'update_plugin_' . self::$slug );
		}

		return false;
	}

	/**
	 * Prints errors if debugging is enabled.
	 *
	 * @since 0.4.1
	 *
	 * @param string|array $message The error message to display. Accepts a single string or an array of strings.
	 * @param string $error_code Optional. A computer-readable string to identify the error.
	 * @return void|false The HTML formatted error message if debug display is enabled and false if not.
	 */
	private function error( $message, $error_code = '500' ) {
		if ( ! WP_DEBUG || ! WP_DEBUG_DISPLAY || ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		if ( is_array( $message ) ) {
			foreach ( $message as $msg ) {
				/* translators: 1: the plugin name, 2: the error message */
				printf( __( '<div class="notice notice-error"><p><strong>%1$s updater error:</strong> %2$s</p></div>', 'wsuwp-help-docs' ), // WPCS: XSS ok.
					esc_html( $this->plugin_meta['Name'] ),
					esc_html( $msg['message'] )
				);
			}
		} else {
			/* translators: 1: the plugin name, 2: the error message */
			printf( __( '<div class="notice notice-error"><p><strong>%1$s updater error:</strong> %2$s</p></div>', 'wsuwp-help-docs' ), // WPCS: XSS ok.
				esc_html( $this->plugin_meta['Name'] ),
				esc_html( $message )
			);
		}
	}

	/**
	 * Checks for a newer version and updates the transient accordingly.
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
	 * @param object $transient Required. The WP `update_plugins` transient.
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
		$this->github_response = get_transient( 'update_plugin_' . self::$slug );

		if ( false === $this->github_response ) {
			$this->github_response = $this->get_repository_details();

			if ( ! is_wp_error( $this->github_response ) && ! empty( $this->github_response['zipball_url'] ) ) {
				// Save results of a successful API call to a 12-hour transient cache.
				set_transient( 'update_plugin_' . self::$slug, $this->github_response, 43200 );
			} else {
				// Save results of an error to a 1-hour transient to prevent overloading the GitHub API.
				set_transient( 'update_plugin_' . self::$slug, 'request-error-wait', 3600 );
			}
		}

		// If we have the plugin details, add them to the update_plugins transient.
		if ( $this->github_response && 'request-error-wait' !== $this->github_response ) {
			// If GitHub version greater than installed version.
			if ( true === version_compare( str_replace( 'v', '', $this->github_response['tag_name'] ), $transient->checked[ $this->basename ], 'gt' ) ) {
				$obj              = new stdClass();
				$obj->slug        = self::$slug;
				$obj->plugin      = $this->basename;
				$obj->new_version = $this->github_response['tag_name'];
				$obj->package     = $this->github_response['zipball_url'];
				$obj->url         = $this->plugin_meta['PluginURI'];

				$transient->response[ $this->basename ] = $obj;
			}
		}

		return $transient;
	}

	/**
	 * Displays the plugin details modal.
	 *
	 * The callback function for the 'plugins_api' WP API filter hook. This
	 * retrieves and displays the plugin release info along with the plugin
	 * meta information in the WordPress details modal window.
	 *
	 * @since 0.4.0
	 *
	 * @param object $result Required. The result object is required for the `plugin_information` action.
	 * @param string $action The type of information being requested from the Plugin Installation API.
	 * @param object $args The Plugin API arguments.
	 * @return object|false The result object with the plugin details added or false.
	 */
	public function display_plugin_details( $result, $action, $args ) {
		// Do nothing if this isn't a request for information.
		if ( 'plugin_information' !== $action ) {
			return false;
		}

		// Return the result now if it isn't our plugin.
		if ( self::$slug !== $args->slug ) {
			return $result;
		}

		// Try to get plugin details from the cache before checking the API.
		$this->github_response = get_transient( 'update_plugin_' . self::$slug );

		if ( false === $this->github_response ) {
			$this->github_response = $this->get_repository_details();

			if ( ! is_wp_error( $this->github_response ) && ! empty( $this->github_response['zipball_url'] ) ) {
				// Save results of a successful API call to a 12-hour transient cache.
				set_transient( 'update_plugin_' . self::$slug, $this->github_response, 43200 );
			} else {
				// Save results of an error to a 1-hour transient to prevent overloading the GitHub API.
				set_transient( 'update_plugin_' . self::$slug, 'request-error-wait', 3600 );
			}
		}

		if ( $this->github_response && 'request-error-wait' !== $this->github_response ) {
			/* translators: 1: the plugin version number, 2: the HTML formatted release message from GitHub */
			$changelog = sprintf( __( '<strong>Version %1$s Changes</strong>%2$s', 'wsuwp-help-docs' ),
				$this->github_response['tag_name'],
				apply_filters( 'the_content', $this->github_response['body'] )
			);

			$result                    = new stdClass();
			$result->name              = $this->plugin_meta['Name'];
			$result->slug              = self::$slug;
			$result->version           = str_replace( 'v', '', $this->github_response['tag_name'] );
			$result->requires          = $this->plugin_meta['Requires at least'];
			$result->tested            = $this->plugin_meta['Tested up to'];
			$result->requires_php      = $this->plugin_meta['Requires PHP'];
			$result->author            = $this->plugin_meta['AuthorName'];
			$result->author_profile    = $this->plugin_meta['AuthorURI'];
			$result->last_updated      = $this->github_response['published_at'];
			$result->homepage          = $this->plugin_meta['PluginURI'];
			$result->short_description = $this->plugin_meta['Description'];
			$result->sections          = array(
				'description' => $this->plugin_meta['Description'],
				'changelog'   => $changelog,
			);
			$result->download_link     = $this->github_response['zipball_url'];

			return $result;
		}

		return false;
	}

	/**
	 * Adds a "view details" link to the plugin row meta.
	 *
	 * Callback function for the `plugin_row_meta` filter hook. This function
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
				esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . self::$slug . '&TB_iframe=true&width=600&height=550' ) ),
				/* translators: the plugin name */
				esc_attr( sprintf( __( 'More information about %s', 'wsuwp-help-docs' ), $this->plugin_meta['Name'] ) ),
				esc_attr( $this->plugin_meta['Name'] ),
				__( 'View details', 'wsuwp-help-docs' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * Adds custom headers for WordPress and PHP version requirements.
	 *
	 * Callback function for the `extra_{$context}_headers` WP Filter hook,
	 * where here `$context` is `plugin`. This hook fires in `get_file_data()`,
	 * called in this context by `get_plugin_data()`. The `get_plugin_data`
	 * function passes the `plugin` context, allowing us to add some custom
	 * headers to the plugin data parser. The values passed here must match
	 * the head matter in the main plugin file, and must be passed as-is to the
	 * `WSUWP_Help_Docs_Updater->display_plugin_details()` results object.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_file_data/
	 * @link https://developer.wordpress.org/reference/functions/get_plugin_data/
	 * @link https://codex.wordpress.org/File_Header
	 *
	 * @since 0.4.1
	 *
	 * @param array $extra_headers List of headers, in the format array('HeaderKey' => 'Header Name').
	 * @return array Array of file headers to add to the default headers array.
	 */
	public function add_plugin_headers( $extra_headers ) {
		$extra_headers = array(
			'RequiresAtLeast' => 'Requires at least',
			'TestedUpTo'      => 'Tested up to',
			'RequiresPHP'     => 'Requires PHP',
		);

		return $extra_headers;
	}

	/**
	 * Moves plugin files back into place after installation and reactivates.
	 *
	 * The callback function for the `upgrader_post_install` WP Filter hook.
	 * When updating the plugin WP downloads and extracts the zip file from the
	 * GitHub response URL. The WP Upgrader class then removes the old plugin
	 * directory and replaces it with the new one. Because this came from GitHub
	 * the name of the new directory will be wrong. This function renames the
	 * new destination directory to match the old plugin directory. It also
	 * reactivates the plugin if it was previously active and flushes the
	 * plugin's transient cache.
	 *
	 * @link https://developer.wordpress.org/reference/classes/wp_upgrader/install_package/
	 *
	 * @since 0.4.1
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @param array $result     Installation result data.
	 * @return array The modified installation result data.
	 */
	public function update_post_install( $response, $hook_extra, $result ) {
		// Retrieve the global WP Filesystem API object.
		global $wp_filesystem;

		$install_dir = plugin_dir_path( $this->file );

		// Rename the installed directory to the plugin directory.
		$wp_filesystem->move( $result['destination'], $install_dir );

		// Set the destination for the rest of the stack.
		$result['destination']      = $install_dir;
		$result['destination_name'] = self::$slug;

		// Reactivate the plugin if it was active.
		if ( $this->active ) {
			activate_plugin( $this->basename );
		}

		// Clear the plugin update cache after upgrade.
		if ( $result ) {
			self::flush_transient_cache();
		}

		return $result;
	}
}
