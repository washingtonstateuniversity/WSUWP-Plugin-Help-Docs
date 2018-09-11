<?php
/**
 * WSUWP Help Docs Setup: WSUWP_Help_Docs class
 *
 * @package WSUWP_Help_Docs
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The WSUWP Help Docs setup class.
 *
 * @since 0.1.0
 */
class WSUWP_Help_Docs {
	/**
	 * The plugin version number.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $version = '0.2.1';

	/**
	 * Slug used to register the post type.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $post_type_slug = 'wsu_help_docs';

	/**
	 * Slug used to handle rewrites.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public $admin_slug = 'help-documents';

	/**
	 * Instantiates WSUWP Help singleton.
	 *
	 * @since 0.1.0
	 *
	 * @return object The WSUWP Help object
	 */
	public static function get_instance() {
		static $instance = null;

		// Only set up and activate the plugin if it hasn't already been done.
		if ( null === $instance ) {
			$instance = new WSUWP_Help_Docs();
			$instance->setup_hooks();
			$instance->includes();
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
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_setup' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_options' ) );
		add_action( 'save_post', array( $this, 'save_post_submitbox_options' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'set_page_link' ), 10, 2 );
	}

	/**
	 * Flushes rewrite rules only on initial activation.
	 *
	 * @since 0.1.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( is_admin() && 'activated' === get_option( 'wsuwp-help-plugin-activated' ) ) {
			delete_option( 'wsuwp-help-plugin-activated' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Activates the WSUWP Help plugin.
	 *
	 * @since 0.1.0
	 */
	public static function activate() {
		/*
		 * Don't love this, but can't see another solution. Need to flush
		 * rewrite rules only after the post type is created, but
		 * register_activation_hook	runs before that.
		 */
		add_option( 'wsuwp-help-plugin-activated', 'activated' );
	}

	/**
	 * Deactivates the WSUWP Help plugin.
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
	 * Registers the WSUWP Help post type.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 *
	 * @since 0.1.0
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Help Documents', 'wsuwp-help-docs' ),
			'singular_name'      => __( 'Help Document', 'wsuwp-help-docs' ),
			'all_items'          => __( 'Help Documents', 'wsuwp-help-docs' ),
			'add_new_item'       => __( 'Add New Help Document', 'wsuwp-help-docs' ),
			'edit_item'          => __( 'Edit Help Document', 'wsuwp-help-docs' ),
			'new_item'           => __( 'New Help Document', 'wsuwp-help-docs' ),
			'view_item'          => __( 'View Help Document', 'wsuwp-help-docs' ),
			'search_items'       => __( 'Search Help Documents', 'wsuwp-help-docs' ),
			'not_found'          => __( 'No Help Documents found', 'wsuwp-help-docs' ),
			'not_found_in_trash' => __( 'No Help Documents found in trash', 'wsuwp-help-docs' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Help details.', 'wsuwp-help-docs' ),
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
		$doc_id = ( isset( $_GET['doc'] ) ) ? absint( $_GET['doc'] ) : '';

		// Verify the requst nonce and return the document ID if successful.
		if ( '' !== $doc_id && isset( $_GET['_wsuwp_wsuwp_help_nonce'] ) ) {
			if ( wp_verify_nonce( $_GET['_wsuwp_wsuwp_help_nonce'], 'wsuwp-help-docs-nav_' . $doc_id ) ) {
				return $doc_id;
			} else {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wsu-wsuwp-help' ) );
			}
		}

		return absint( get_option( 'wsuwp_help_homepage_id', 0 ) );
	}

	/**
	 * Enqueues the plugin admin styles.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wsuwp-help-dashboard', plugins_url( 'css/dashboard.css', __DIR__ ), array(), $this->version );
	}

	/**
	 * Constructs the admin page URL.
	 *
	 * @since 0.1.0
	 *
	 * @return string The absolute URL of the plugin WP Admin dashboard page.
	 */
	public function get_admin_page_url() {
		return admin_url( 'index.php?page=' . $this->admin_slug );
	}

	/**
	 * Constructs the Help document page URL with parameters.
	 *
	 * The WSU WSUWP Help plugin uses a URL parameter to retrieve the requested
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

	/**
	 * Adds a new dashboard widget for the Help plugin.
	 *
	 * This adds a widget on the main WP dashboard page to provide some
	 * information about the Help documents.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_add_dashboard_widget
	 *
	 * @since 0.2.0
	 */
	public function dashboard_setup() {
		if ( ! current_user_can( 'read' ) ) {
			return false;
		}
		wp_add_dashboard_widget( 'dashboard_wsu_help_docs', 'Help', array( $this, 'display_help_dashboard_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Displays the admin dashboard widget.
	 *
	 * @since 0.2.0
	 */
	public function display_help_dashboard_widget() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wsu-wsuwp-help' ) );
		}

		$docs = get_posts( array(
			'numberposts' => 5,
			'post_type'   => self::$post_type_slug,
		) );
		?>
		<div id="wsuwp-help-welcome">
			<?php
			/* translators: the help documents dashboard page URL */
			printf( __( '<p>Visit the <a href="%s">Help documents</a> for how-to guides and instructions.</p>', 'wsu-wsuwp-help' ), // WPCS: XSS ok.
				esc_url( $this->get_admin_page_url() )
			);
			?>
		</div>
		<div id="wsuwp-help-updated" class="activity-block">
			<h3><strong><?php esc_html_e( 'New and Updated Help Documents', 'wsu-wsuwp-help' ); ?></strong></h3>
			<?php
			if ( ! empty( $docs ) ) {
				echo '<ul class="wsuwp-updated-help-documents-list">';
				foreach ( $docs as $doc ) {
					/* translators: 1: the help document url, 2: the help document title, 3: the help document modified date. */
					printf( __( '<li><a href="%1$s">%2$s</a><span>Updated %3$s</span></li>', 'wsu-wsuwp-help' ), // WPCS: XSS ok.
						esc_html( wp_nonce_url( get_permalink( $doc->ID ), 'wsuwp-help-docs-nav_' . absint( $doc->ID ), '_wsuwp_wsuwp_help_nonce' ) ),
						esc_html( get_the_title( $doc->ID ) ),
						esc_html( get_the_modified_date() )
					);
				}
				echo '</ul>';
			}
			?>
		</div>
		<div id="wsuwp-help-access" class="activity-block">
			<svg class="wsu-cougar-head" xmlns="http://www.w3.org/2000/svg" width="50.428" height="50" viewBox="7.481 7.416 50.428 50">
				<g fill="#82878c"><path d="M38.202 57.372s2.547-1.089 3.948-5.315a10.398 10.398 0 0 1 .903 5.211 33.15 33.15 0 0 1-4.851.104zm10.665-8.489c-7.457 1.088-8.742-14.603-8.742-14.603s2.5 8.002 7.827 7.712c5.511-.277 3.948-8.766 3.948-8.766s5.361 14.499-3.033 15.657zm-34.218-3.544a64.527 64.527 0 0 1-7.168 1.69s4.261-3.22 7.435-13.179l3.068 2.769-.579 1.899a14.269 14.269 0 0 1 1.83 3.474 9.15 9.15 0 0 0-.198-8.025l-.393 1.16-1.159-1.09-2.061-1.887a25.87 25.87 0 0 1 3.844-7.238l.278.302 2.316 2.744-.707 1.159a30.013 30.013 0 0 1 2.999 3.809 17.36 17.36 0 0 0-.289-7.249l-1.065.997-2.628-3.046a29.047 29.047 0 0 1 11.649-7.411c-.27.294-.518.608-.742.938-1.471 2.061-2.952 5.789-1.749 11.799.175.949.499 2.317.823 3.683.635 2.744 1.354 5.858 1.609 7.815.509 4.088.092 6.764-1.263 8.187a5.886 5.886 0 0 1-4.632 1.343v-.972a20.997 20.997 0 0 0-.614-5.165l-.566-1.98-.892 1.842c-1.401 2.919-6.172 10.168-12.379 11.58a20.909 20.909 0 0 0 3.233-9.948z"></path><path d="M50.429 18.394l-.068-.405 7.179-1.436-.128-.8-7.398.926c0-.139-.093-.29-.151-.429l6.764-2.628-.29-.706-7.075 2.188a3.327 3.327 0 0 0-1.331-1.402c-2.699.209-5.143.429-7.377.719l1.413-6.624h-.684l-2.166 6.832-1.157.197 1.688-7.411h-.798l-2.49 7.712h-.093a7.88 7.88 0 0 0-3.925 2.988c-1.286 1.83-2.594 5.154-1.505 10.642.196.915.498 2.224.822 3.625.647 2.779 1.376 5.94 1.631 7.979.592 4.632 0 7.632-1.679 9.427-1.4 1.459-3.567 2.039-6.601 1.771l-.764-.069.07-.754c.051-.538.074-1.079.069-1.62 0-.919-.063-1.835-.185-2.746a26.863 26.863 0 0 1-8.616 8.987c5.319.24 10.619.813 15.865 1.712l.845.081h.29c.631.017 1.262-.014 1.887-.092h.302c3.682-.636 6.866-3.357 6.01-9.67-1.007-7.492-2.316-12.261-2.652-15.864-.404-4.376 3.394-11.162 10.145-8.105a10.75 10.75 0 0 1 2.165 3.66 10.114 10.114 0 0 0-.207-4.631 7.793 7.793 0 0 0 .311-2.699l7.365-.312v-.811l-7.481-.232zm-7.48 2.686a10.608 10.608 0 0 0-6.38 1.668 8.502 8.502 0 0 0-2.408 2.767 4.633 4.633 0 0 1 1.887-4.493 9.672 9.672 0 0 1 7.364-.383c.648.383.185.487-.463.441z"></path></g>
			</svg>
			<?php if ( current_user_can( 'publish_posts' ) ) : ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' ) . self::$post_type_slug ); ?>">
					<?php echo esc_html_x( 'Manage', 'verb. Button with limited space', 'wsu-wsuwp-help' ); ?>
				</a>
			<?php endif; ?>
			<a class="button button-primary" href="<?php echo esc_url( $this->get_admin_page_url() ); ?>">
				<?php esc_html_e( 'View Help', 'wsu-wsuwp-help' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Adds options to the Help post submit box.
	 *
	 * Callback function for the `post_submitbox_misc_actions` action hook.
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/post_submitbox_misc_actions
	 *
	 * @since 0.2.0
	 */
	public function post_submitbox_options() {
		$post = get_post();

		if ( self::$post_type_slug !== $post->post_type || ! current_user_can( 'publish_posts' ) ) {
			return $post->ID;
		}

		// Add nonce.
		wp_nonce_field( 'postbox-actions_' . $post->ID, '_wsuwp_help_postbox_actions_nonce' );
		?>
		<div class="misc-pub-section">
			<input type="checkbox" name="wsuwp_help_homepage_select" id="wsuwp_help_homepage_select" <?php checked( absint( get_option( 'wsuwp_help_homepage_id' ) ) === $post->ID ); ?> />
			<label for="wsuwp_help_homepage_select"><?php echo esc_html__( 'Set as Help home', 'wsu-wsuwp-help' ); ?></label>
		</div>
		<?php
	}

	/**
	 * Handles saving Help postbox options on post save or update.
	 *
	 * @since 0.2.0
	 *
	 * @param int $post_id The Post ID.
	 * @return int|bool Post ID on check failure, True if option value has
	 *                  changed, false if not or if update failed.
	 */
	public function save_post_submitbox_options( $post_id ) {
		// Run security checks.
		if ( ! isset( $_POST['_wsuwp_help_postbox_actions_nonce'] ) || ! wp_verify_nonce( $_POST['_wsuwp_help_postbox_actions_nonce'], 'postbox-actions_' . $post_id ) ) {
			return $post_id;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check if not autosave or AJAX.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $post_id;
		}

		if ( isset( $_POST['wsuwp_help_homepage_select'] ) ) {
			// Update default Help document if selected.
			update_option( 'wsuwp_help_homepage_id', absint( $post_id ) );
		} elseif ( absint( get_option( 'wsuwp_help_homepage_id' ) ) === $post_id ) {
			// Unset default Help document if active and deselected.
			update_option( 'wsuwp_help_homepage_id', 0 );
		}
	}

}

/**
 * Creates an instance of the WSUWP Help class.
 *
 * Use this function like you might use a global variable or a direct call to
 * `WSUWP_Help_Docs::get_instance()`.
 *
 * @since 0.1.0
 *
 * @return object The single WSUWP Help instance.
 */
function load_wsuwp_help() {
	return WSUWP_Help_Docs::get_instance();
}

load_wsuwp_help();
