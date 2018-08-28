<?php
/**
 * Uninstall HRS Help: Uninstall_WSU_HRS_Help class
 *
 * Uninstall will remove all options and delete all posts created by the HRS
 * Help custom post type plugin. Do not need to flush cache/temp or permalinks
 * here, as that will have already been done on deactivation. Uses get_posts()
 * and wp_trash_post() to do the heavy lifting. get_posts() does not return
 * posts with of auto_draft type, however, so currently these methods will not
 * delete any auto drafts from the database.
 *
 * @todo Consider switching to using a $wpdb SQL query to more thoroughly delete
 *       all HRS Help custom post types.
 *
 * @package WSUWP_HRS_Help
 * @since 0.1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( ! class_exists( 'Uninstall_WSU_HRS_Help' ) ) :
	/**
	 * The HRS Help uninstall class.
	 *
	 * @since 0.1.0
	 */
	class Uninstall_WSU_HRS_Help {
		/**
		 * Runs through the uninstall functions.
		 *
		 * @since 0.1.0
		 */
		public function run_uninstaller() {
			// Remove options if not already gone.
			if ( get_option( 'hrs-help-plugin-activated' ) ) {
				delete_option( 'hrs-help-plugin-activated' );
			}

			// Delete HRS Help posts.
			$this->delete_hrs_help_posts();
		}

		/**
		 * Gets post ids of the HRS Help post types.
		 *
		 * @since 0.1.0
		 *
		 * @param int $limit Limit how many ids are returned. Default 800.
		 * @return array Array of post ids.
		 */
		private function hrsn_get_post_ids( $limit = 800 ) {
			global $post;

			if ( ! absint( $limit ) ) {
				return array();
			}

			$args = array(
				'post_type'   => 'hrs_help',
				'numberposts' => absint( $limit ),
			);

			$hrs_posts = get_posts( $args );

			$ids = array();
			foreach ( $hrs_posts as $p ) {
				$ids[] += $p->ID;
			}

			return $ids;
		}

		/**
		 * Trashes HRS Help posts.
		 *
		 * @since 0.1.0
		 *
		 * @todo Currently only deletes up to the limit specified in
		 *       hrsn_get_post_ids. Need to find a way to delete more without
		 *       introducing timeout errors.
		 */
		private function delete_hrs_help_posts() {
			// Get the HRS Help post ID list
			$hrsn_id_list = $this->hrsn_get_post_ids();

			// Move selected posts to trash
			foreach ( $hrsn_id_list as $hrsn_id ) {
				wp_trash_post( $hrsn_id );
			}
		}
	}
endif;

$uninstall_hrs_help = new Uninstall_WSU_HRS_Help();
$uninstall_hrs_help->run_uninstaller();
