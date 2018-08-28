<?php
/**
 * WSUWP HRS Help Setup: WSU_HRS_Help class
 *
 * @package WSUWP_HRS_Help
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The WSUWP HRS Help setup class.
 *
 * @since 0.1.0
 */
class WSU_HRS_Help {
	/**
	 * The plugin version number.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected static $version = '0.1.0';

	/**
	 * The ID of the currently viewed help document.
	 *
	 * @since 0.1.0
	 * @var int
	 */
	protected static $current_help_doc = 1;

	/**
	 * Slug used to register the post type.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $post_type_slug = 'wsu_hrs_help';

	/**
	 * Slug used to handle rewrites.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $admin_slug = 'help-documents';

	/**
	 * Instantiates HRS Help singleton.
	 *
	 * @since 0.1.0
	 *
	 * @return object The HRS Help object
	 */
	public static function get_instance() {
		static $instance = null;

		// Only set up and activate the plugin if it hasn't already been done.
		if ( null === $instance ) {
			$instance = new WSU_HRS_Help();
			$instance->setup_hooks();
			$instance->includes();
		}

		return $instance;
	}

	/**
	 * An empty constructor to prevent HRS Help being loaded more than once.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		/* Nothing doing. */
	}

	/**
	 * Includes the required files.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 */
	private function includes() {
		require __DIR__ . '/class-page-list-walker.php';
	}

	/**
	 * Loads the WP API actions and hooks.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'maybe_flush_rewrite_rules' ) );
		add_action( 'admin_menu', array( $this, 'help_menu' ) );
		add_filter( 'post_type_link', array( $this, 'set_page_link' ), 10, 2 );
	}

	/**
	 * Flushes rewrite rules only on initial activation.
	 *
	 * @since 0.1.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( is_admin() && 'activated' === get_option( 'hrs-help-plugin-activated' ) ) {
			delete_option( 'hrs-help-plugin-activated' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Activates the HRS Help plugin.
	 *
	 * @since 0.1.0
	 */
	public static function activate() {
		/*
		 * Don't love this, but can't see another solution. Need to flush
		 * rewrite rules only after the post type is created, but
		 * register_activation_hook	runs before that.
		 */
		add_option( 'hrs-helps-plugin-activated', 'activated' );
	}

	/**
	 * Deactivates the HRS Help plugin.
	 *
	 * @since 0.1.0
	 */
	public static function deactivate() {
		// Deregister custom post type, taxonomy, and shortcode (remove rules from memory).
		unregister_post_type( self::$post_type_slug );

		// Flush rewrite rules on plugin deactivation to remove custom permalinks.
		flush_rewrite_rules();
	}

	/**
	 * Registers the HRS Help post type.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 *
	 * @since 0.1.0
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Help Documents', 'wsuwp-hrs-help' ),
			'singular_name'      => __( 'Help Document', 'wsuwp-hrs-help' ),
			'all_items'          => __( 'Help Documents', 'wsuwp-hrs-help' ),
			'add_new_item'       => __( 'Add New Help Document', 'wsuwp-hrs-help' ),
			'edit_item'          => __( 'Edit Help Document', 'wsuwp-hrs-help' ),
			'new_item'           => __( 'New Help Document', 'wsuwp-hrs-help' ),
			'view_item'          => __( 'View Help Document', 'wsuwp-hrs-help' ),
			'search_items'       => __( 'Search Help Documents', 'wsuwp-hrs-help' ),
			'not_found'          => __( 'No Help Documents found', 'wsuwp-hrs-help' ),
			'not_found_in_trash' => __( 'No Help Documents found in trash', 'wsuwp-hrs-help' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Help details.', 'wsuwp-hrs-help' ),
			'public'              => true,
			'show_in_menu'        => 'tools.php',
			'show_in_admin_bar'   => false,
			'show_in_rest'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'hierarchical'        => true,
			'supports'            => array(
				'title',
				'author',
				'editor',
				'page-attributes',
				'revisions',
			),
		);

		register_post_type( self::$post_type_slug, $args );
	}

	/**
	 * Retrieves the ID of the current requested Help document.
	 *
	 * Checks the URL for a document ID and returns it if it passes nonce
	 * verification. Otherwise it returns the default document ID.
	 *
	 * @since 0.1.0
	 *
	 * @return int The ID of the current document requested or the default.
	 */
	public static function get_current_help_doc_id() {
		// Check for a requested help document.
		$doc = ( isset( $_GET['doc'] ) ) ? absint( $_GET['doc'] ) : '';

		// Verify the requst nonce and save the document ID if successful.
		if ( isset( $_GET['_wsuwp_hrs_help_nonce'] ) ) {
			if ( wp_verify_nonce( $_GET['_wsuwp_hrs_help_nonce'], 'wsuwp-hrs-help-nav_' . $doc ) ) {
				self::$current_help_doc = $doc;
			} else {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wsu-hrs-help' ) );
			}
		}

		return self::$current_help_doc;
	}

	/**
	 * Enqueues the plugin admin styles.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'hrs-help-dashboard', plugins_url( 'css/dashboard.css', __DIR__ ), array(), self::$version );
	}

	/**
	 * Constructs the admin page URL.
	 *
	 * @since 0.1.0
	 *
	 * @return string The absolute URL of the plugin WP Admin dashboard page.
	 */
	public function get_admin_page_url() {
		return admin_url( 'index.php?page=' . self::$admin_slug );
	}

	/**
	 * Constructs the Help document page URL with parameters.
	 *
	 * The WSU HRS Help plugin uses a URL parameter to retrieve the requested
	 * Help document. This function is a callback for the `post_type_link`
	 * filter {@see https://codex.wordpress.org/Plugin_API/Filter_Reference/post_type_link},
	 * which alters the permalink URL for the custom post type.
	 *
	 * @since 0.1.0
	 *
	 * @return string The absolute URL of the Help document page.
	 */
	public function set_page_link( $link, $post ) {
		$post = get_post( $post );

		// If requesting a Help document, add the URL parameter.
		if ( self::$post_type_slug === $post->post_type ) {
			return $this->get_admin_page_url() . '&doc=' . absint( $post->ID );
		} else {
			return $link;
		}
	}

	/**
	 * Adds a new dashboard page for the Help plugin with load hooks.
	 *
	 * This creates a new submenu under the Dashboard section of the main admin
	 * menu. It also adds a callback to the `load-{admin page}` hook that fires
	 * whenever the new dashboard page is loaded. This function is a callback
	 * for the `admin_menu` action that adds extra submenus to the admin panel's
	 * menu structure {@see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu}.
	 *
	 * @since 0.1.0
	 */
	public function help_menu() {
		$hook = add_dashboard_page( 'Help Dashboard', 'Help', 'read', 'help-documents', array( $this, 'display_help_dashboard' ) );
		add_action( 'load-' . $hook, array( $this, 'load_help_dashboard_cb' ) );
	}

	/**
	 * Retrieves the admin dashboard page template.
	 *
	 * The Help plugin admin dashboard page displays a navigation menu of all
	 * published Help documents and retrieves and displays the content of the
	 * current Help document. This function is a callback for
	 * `add_dashboard_page()`, called in the `$this->help_menu()` function.
	 *
	 * @since 0.1.0
	 */
	public function display_help_dashboard() {
		/**
		 * Loads the admin dashboard page.
		 *
		 * @since 0.1.0
		 */
		include plugin_dir_path( __DIR__ ) . 'templates/dashboard-page.php';
	}

	/**
	 * Adds the Help plugin scripts to the admin dashboard page.
	 *
	 * @since 0.1.0
	 */
	public function load_help_dashboard_cb() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

}

/**
 * Creates an instance of the HRS Help class.
 *
 * Use this function like you might use a global variable or a direct call to
 * `WSU_HRS_Help::get_instance()`.
 *
 * @since 0.1.0
 *
 * @return object The single HRS Help instance.
 */
function load_hrs_help() {
	return WSU_HRS_Help::get_instance();
}

load_hrs_help();
