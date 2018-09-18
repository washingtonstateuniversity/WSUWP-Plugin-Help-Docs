<?php
/**
 * Uninstall WSUWP Help Docs: Uninstall_WSUWP_Help_Docs class
 *
 * Uninstall will remove all options and delete all posts created by the WSUWP
 * Help Docs plugin. We don't need to flush cache/temp or permalinks here, as
 * that will have already been done on deactivation. Uses get_posts() and
 * wp_trash_post() to do the heavy lifting. get_posts() does not return posts
 * with of auto_draft type, however, so currently these methods will not delete
 * any auto drafts from the database.
 *
 * @todo Consider switching to using a $wpdb SQL query to more thoroughly delete
 *       all WSUWP Help custom post types.
 *
 * @package WSUWP_Help_Docs
 * @since 0.1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( ! class_exists( 'Uninstall_WSUWP_Help_Docs' ) ) :
	/**
	 * The WSUWP Help uninstall class.
	 *
	 * @since 0.1.0
	 */
	class Uninstall_WSUWP_Help_Docs {
		/**
		 * Runs through the uninstall functions.
		 *
		 * @since 0.1.0
		 */
		public function run_uninstaller() {
			// Remove options if not already gone.
			if ( get_option( 'wsuwp-help-plugin-activated' ) ) {
				delete_option( 'wsuwp-help-plugin-activated' );
			}
			if ( get_option( 'wsuwp_help_homepage_id' ) ) {
				delete_option( 'wsuwp_help_homepage_id' );
			}

			// Delete WSUWP Help posts.
			$this->delete_wsuwp_help_posts();
		}

		/**
		 * Gets post ids of the WSUWP Help post types.
		 *
		 * @since 0.1.0
		 *
		 * @param int $limit Limit how many ids are returned. Default 800.
		 * @return array Array of post ids.
		 */
		private function wsuwp_help_get_post_ids( $limit = 800 ) {
			global $post;

			if ( ! absint( $limit ) ) {
				return array();
			}

			$wsuwp_help_posts = get_posts( array(
				'post_type'   => 'wsu_help_docs',
				'numberposts' => absint( $limit ),
			) );

			$ids = array();
			foreach ( $wsuwp_help_posts as $p ) {
				$ids[] += $p->ID;
			}

			return $ids;
		}

		/**
		 * Trashes WSUWP Help posts.
		 *
		 * @since 0.1.0
		 *
		 * @todo Currently only deletes up to the limit specified in
		 *       wsuwp_help_get_post_ids. Need to find a way to delete more without
		 *       introducing timeout errors.
		 */
		private function delete_wsuwp_help_posts() {
			// Get the WSUWP Help post ID list
			$help_docs_id_list = $this->wsuwp_help_get_post_ids();

			// Move selected posts to trash
			foreach ( $help_docs_id_list as $doc_id ) {
				wp_trash_post( $doc_id );
			}
		}
	}
endif;

$uninstall_wsuwp_help = new Uninstall_WSUWP_Help_Docs();
$uninstall_wsuwp_help->run_uninstaller();
