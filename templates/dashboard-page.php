<?php
/**
 * WSUWP Help Plugin Dashboard Template
 *
 * A template that displays the main WSUWP Help Dashboard page, which provides a
 * navigation list of all published Help posts and displays the current
 * requested post document.
 *
 * @package WSUWP_Help_Docs
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>

<div class="wrap wsuwp-help">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Help Documents', 'wsuwp-help-docs' ); ?></h1>

	<?php
	// Stop execution if the user doesn't have read permissions.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wsuwp-help-docs' ) );
	}

	if ( current_user_can( 'publish_posts' ) ) {
		?>
		<a class="page-title-action" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' ) . self::$post_type_slug ); ?>">
			<?php echo esc_html_x( 'Manage', 'verb. Button with limited space', 'wsu-wsuwp-help' ); ?>
		</a>
		<?php
	}
	?>

	<hr class="wp-header-end">

	<div class="wsuwp-help-documents-wrap">
		<nav id="wsuwp-help-documents-menu">
			<ul>
				<?php
				/*
				 * List all published Help documents using a custom walker class
				 * that uses `get_current_help_doc_id()` to output classes for
				 * things like the current page.
				 */
				wp_list_pages(
					array(
						'post_type'    => self::$post_type_slug,
						'hierarchical' => true,
						'title_li'     => '',
						'walker'       => new Walker_WSUWP_Help_Page_List(),
					)
				);
				?>
			</ul>
		</nav>

		<section class="wsuwp-help-documents">
			<?php
			// Retrieve the current requested help document ID.
			$help_doc_id = $this->get_current_help_doc_id();

			if ( '' !== $help_doc_id && 0 !== $help_doc_id ) :
				$docs = new WP_Query(
					array(
						'post_type' => self::$post_type_slug,
						'p'         => $help_doc_id,
					)
				);

				if ( $docs->have_posts() ) {
					while ( $docs->have_posts() ) {
						$docs->the_post();

						?>
						<article class="wsuwp-help-document" id="wsuwp-help-document-<?php the_ID(); ?>">
							<header class="article-header">
								<h2 class="article-title"><?php the_title(); ?></h2>
								<p><small><em><?php echo esc_html__( 'Last updated:', 'wsuwp-help-docs' ); ?></em> <time class="article-modify-date" datetime="<?php esc_attr( the_modified_date( 'c' ) ); ?>"><?php esc_html( the_modified_date() ); ?></time></small></p>
							</header>
							<div class="article-body">
								<?php
								the_content();

								if ( current_user_can( 'edit_others_posts' ) ) {
									edit_post_link( __( 'Edit', 'wsuwp-help-docs' ), ' <pre>', '</pre>' );
								}
								?>
							</div>
						</article>
						<?php

					};
					wp_reset_postdata();
				} else {
					?>
					<article class="wsuwp-help-document">
						<header class="article-header">
							<h2 class="article-title"><?php __( 'No documents found', 'wsuwp-help-docs' ); ?></h2>
						</header>
					</article>
					<?php
				}
			elseif ( 0 === $help_doc_id ) :
				?>
				<article class="wsuwp-help-document" id="wsuwp-help-document-home">
					<header class="article-header">
						<h2 class="article-title"><?php echo esc_html__( 'Welcome to the Help Dashboard', 'wsuwp-help-docs' ); ?></h2>
					</header>
					<div class="article-body">
						<p><?php echo esc_html__( 'This is the home dashboard of the Help Documents.', 'wsuwp-help-docs' ); ?></p>
						<?php if ( current_user_can( 'publish_posts' ) ) : ?>
							<p><?php echo esc_html__( 'You can set any Help document as this home page by selecting the &ldquo;Set as help home&rdquo; option on the Edit Help Document screen, under Tools > Help Documents.', 'wsuwp-help-docs' ); ?></p>
							<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' ) . self::$post_type_slug ); ?>">
								<?php echo esc_html__( 'Get started editing', 'wsuwp-help-docs' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</article>
				<?php
			else :
				?>
				<article class="wsuwp-help-document">
					<header class="article-header">
						<h2><?php __( 'No documents found', 'wsuwp-help-docs' ); ?></h2>
					</header>
				</article>
				<?php
			endif;
			?>
		</section>
	</div>
</div>
