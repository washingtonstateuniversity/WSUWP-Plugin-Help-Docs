<?php
/**
 * HRS Help Plugin Dashboard Template
 *
 * A template that displays the main HRS Help Dashboard page, which provides a
 * navigation list of all published Help posts and displays the current
 * requested post document.
 *
 * @package WSUWP_HRS_Help
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>

<div class="wrap hrs-help">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Help Documents', 'hrs-wsu-edu' ); ?></h1>

	<?php
	// Stop execution if the user doesn't have read permissions.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wsu-hrs-help' ) );
	}

	if ( current_user_can( 'publish_posts' ) ) {
		?>
		<a class="page-title-action" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' ) . self::$post_type_slug ); ?>">
			<?php echo esc_html_x( 'Manage', 'verb. Button with limited space', 'wsu-hrs-help' ); ?>
		</a>
		<?php
	}
	?>

	<hr class="wp-header-end">

	<div class="hrs-help-documents-wrap">
		<nav class="hrs-help-documents-list">
			<ul>
				<?php
				/*
				 * List all published Help documents using a custom walker class
				 * that uses `get_current_help_doc_id()` to output classes for
				 * things like the current page.
				 */
				wp_list_pages( array(
					'post_type'    => self::$post_type_slug,
					'hierarchical' => true,
					'title_li'     => '',
					'walker'       => new Walker_HRS_Help_Page_List(),
				) );
				?>
			</ul>
		</nav>

		<section class="hrs-help-documents">
			<?php
			// Retrieve the current requested help document ID.
			$help_doc_id = $this->get_current_help_doc_id();

			if ( '' !== $help_doc_id && 1 !== $help_doc_id ) :
				$docs = new WP_Query( array(
					'post_type' => self::$post_type_slug,
					'p'         => $help_doc_id,
				) );

				if ( $docs->have_posts() ) :
					while ( $docs->have_posts() ) : $docs->the_post();
						?>
						<article class="hrs-help-document" id="hrs-help-document-<?php the_ID(); ?>">
							<header class="article-header">
								<h2><?php the_title(); ?></h2>
								<p><small><em><?php echo esc_html__( 'Last updated:', 'wsu-hrs-edu' ); ?></em> <time class="article-modify-date" datetime="<?php esc_attr( the_modified_date( 'c' ) ); ?>"><?php esc_html( the_modified_date() ); ?></time></small></p>
							</header>
							<div class="article-body">
								<?php
								the_content();

								if ( current_user_can( 'edit_others_posts' ) ) {
									edit_post_link( __( 'Edit', 'wsu-hrs-help' ), ' <pre>', '</pre>' );
								}
								?>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<article class="hrs-help-document">
						<header class="article-header">
							<h2><?php __( 'No documents found', 'wsu-hrs-help' ); ?></h2>
						</header>
					</article>
					<?php
				endif;
			elseif ( 1 === $help_doc_id ) :
				?>
				<article class="hrs-help-document" id="hrs-help-document-home">
					<header class="article-header">
						<h2><?php echo esc_html__( 'Welcome', 'wsu-hrs-edu' ); ?></h2>
					</header>
					<div class="article-body">
						<?php echo wp_kses_post( wpautop( 'This is the dashboard of the WSU HRS Help Plugin.' ) ); ?>
					</div>
				</article>
				<?php
			else :
				?>
				<article class="hrs-help-document">
					<header class="article-header">
						<h2><?php __( 'No documents found', 'wsu-hrs-help' ); ?></h2>
					</header>
				</article>
				<?php
			endif;
			?>
		</section>
	</div>
</div>
