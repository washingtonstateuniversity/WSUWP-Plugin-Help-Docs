<?php
/**
 * Custom Post API: Walker_WSUWP_Help_Page_List class
 *
 * @package WSUWP_Help_Docs
 * @since 0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Extention of the core Walker class used to create HTML lists of pages.
 *
 * This is essentially a duplicate of the default Walker_Page::start_el()
 * method. It is only modified to account for the lack of a `$current_page`
 * object. We use `WSUWP_Help_Docs::get_current_help_doc_id()` to set the value
 * of `$current_page` so that the current page conditional classes work as
 * expected. We've also added a nonce verification to the page permalink to aid
 * in verifying in the dashboard template that requests come from the right
 * place.
 *
 * @since 0.1.0
 *
 * @see Walker_Page
 */
class Walker_WSUWP_Help_Page_List extends Walker_Page {
	/**
	 * Outputs the beginning of the current element in the tree.
	 *
	 * @see Walker_Page::start_el()
	 * @since 0.1.0
	 *
	 * @param string  $output       Used to append additional content. Passed by reference.
	 * @param WP_Post $page         Page data object.
	 * @param int     $depth        Optional. Depth of page. Used for padding. Default 0.
	 * @param array   $args         Optional. Array of arguments. Default empty array.
	 * @param int     $current_page Optional. Page ID. Default 0.
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $current_page = 0 ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
		} else {
			$t = '';
		}

		if ( $depth ) {
			$indent = str_repeat( $t, $depth );
		} else {
			$indent = '';
		}

		$css_class = array( 'page_item', 'page-item-' . $page->ID );

		if ( isset( $args['pages_with_children'][ $page->ID ] ) ) {
			$css_class[] = 'page_item_has_children';
		}

		// Manually retrieve the current page ID.
		$current_page = WSUWP_Help_Docs::get_current_help_doc_id();

		if ( ! empty( $current_page ) ) {
			$_current_page = get_post( $current_page );
			if ( $_current_page && in_array( $page->ID, $_current_page->ancestors, true ) ) {
				$css_class[] = 'current_page_ancestor';
			}
			if ( $page->ID === $current_page ) {
				$css_class[] = 'current_page_item';
			} elseif ( $_current_page && $page->ID === $_current_page->post_parent ) {
				$css_class[] = 'current_page_parent';
			}
		} elseif ( get_option( 'page_for_posts' ) === $page->ID ) {
			$css_class[] = 'current_page_parent';
		}

		/**
		 * Filters the list of CSS classes to include with each page item in the list.
		 *
		 * @since 0.1.0
		 *
		 * @see wp_list_pages()
		 *
		 * @param array   $css_class    An array of CSS classes to be applied
		 *                              to each list item.
		 * @param WP_Post $page         Page data object.
		 * @param int     $depth        Depth of page, used for padding.
		 * @param array   $args         An array of arguments.
		 * @param int     $current_page ID of the current page.
		 */
		$css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		if ( '' === $page->post_title ) {
			/* translators: %d: ID of a post */
			$page->post_title = sprintf( __( '#%d (no title)', 'wsuwp-help-docs' ), $page->ID );
		}

		$args['link_before'] = empty( $args['link_before'] ) ? '' : $args['link_before'];
		$args['link_after']  = empty( $args['link_after'] ) ? '' : $args['link_after'];

		$atts = array();

		// Add a nonce value to the page permalink for verification.
		$atts['href'] = wp_nonce_url( get_permalink( $page->ID ), 'wsuwp-help-docs-nav_' . $page->ID, '_wsuwp_wsuwp_help_nonce' );

		/**
		 * Filters the HTML attributes applied to a page menu item's anchor element.
		 *
		 * @since 0.1.0
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $href The href attribute.
		 * }
		 * @param WP_Post $page         Page data object.
		 * @param int     $depth        Depth of page, used for padding.
		 * @param array   $args         An array of arguments.
		 * @param int     $current_page ID of the current page.
		 */
		$atts = apply_filters( 'page_menu_link_attributes', $atts, $page, $depth, $args, $current_page );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$output .= $indent . sprintf(
			'<li class="%s"><a%s>%s%s%s</a>',
			$css_classes,
			$attributes,
			$args['link_before'],
			/** This filter is documented in wp-includes/post-template.php */
			apply_filters( 'the_title', $page->post_title, $page->ID ),
			$args['link_after']
		);

		if ( ! empty( $args['show_date'] ) ) {
			if ( 'modified' === $args['show_date'] ) {
				$time = $page->post_modified;
			} else {
				$time = $page->post_date;
			}

			$date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
			$output     .= ' ' . mysql2date( $date_format, $time );
		}
	}

}
