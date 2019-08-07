/**
 * WordPress dependencies.
 */
const {
	select,
	subscribe,
	dispatch,
} = wp.data;

/**
 * A function to display notices on post save and update.
 *
 * See:
 *   - A very useful overview of the block editor's data management: https://riad.blog/2018/06/07/efficient-client-data-management-for-wordpress-plugins/
 *   - A sort-of-helpful bit of documentation: https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/notices/
 *   - Also helpful: https://rsvpmaker.com/blog/2019/03/07/my-gutenberg-breakthrough-adding-a-custom-notification/
 *   - The WP data package: https://wordpress.org/gutenberg/handbook/designers-developers/developers/data/data-core-editor/
 *   - For the logic to show only once after saving: https://github.com/WordPress/gutenberg/blob/master/packages/edit-post/src/store/effects.js
 *   - For the notice syntax: https://github.com/WordPress/gutenberg/blob/master/packages/notices/src/store/actions.js
 *
 * Inspect the WP Data available to the editor by using wp.data.select( 'core/editor' ); from the browser dev tools console.
 */
const helpDocsUpdateNotices = () => {
	const post = select( 'core/editor' ).getCurrentPost();

	const noticeMeta = {
		id: 'wsuwp_help_docs_notice',
		wasSavingPost: select( 'core/editor' ).isSavingPost(),
		wasAutosavingPost: select( 'core/editor' ).isAutosavingPost(),
		wasPublishingPost: select( 'core/editor' ).isPublishingPost(),
		wasStatus: post.status,
	};

	subscribe( () => {
		const isSavingPost = select( 'core/editor' ).isSavingPost();
		const isAutosavingPost = select( 'core/editor' ).isAutosavingPost();
		const isPublishingPost = select( 'core/editor' ).isPublishingPost();
		const isStatus = select( 'core/editor' ).getCurrentPost().status;

		// console.log( `Was status: ${noticeMeta.wasStatus}` ); // DEBUG:
		// console.log( `Is status: ${isStatus}` ); // DEBUG:

		const shouldTriggerAdminNotice = (
			(
				noticeMeta.wasSavingPost &&
				! isSavingPost &&
				! noticeMeta.wasAutosavingPost &&
				! isPublishingPost &&
				! noticeMeta.wasPublishingPost
			)
		);

		noticeMeta.wasSavingPost = isSavingPost;
		noticeMeta.wasAutosavingPost = isAutosavingPost;
		noticeMeta.wasPublishingPost = isPublishingPost;
		noticeMeta.wasStatus = isStatus;

		if ( shouldTriggerAdminNotice ) {
			if ( select( 'core/editor' ).didPostSaveRequestSucceed() ) {
				// if is preview or scheduled
				const link = select( 'core/editor' ).getEditedPostPreviewLink();

				dispatch( 'core/notices' ).createSuccessNotice(
					'Fish?',
					{
						id: noticeMeta.id, // use SAVE_POST_NOTICE_ID to maybe overwrite the existing one?
						actions: [
							{
								url: link,
								label: 'Preview post',
							},
						],
					}
				);
				// DEBUG: This should work, but where to put it?
				// dispatch( 'core/notices' ).removeNotice( 'SAVE_POST_NOTICE_ID' );
			} else if ( select( 'core/editor' ).didPostSaveRequestFail() ) {
				// console.log( 'Fail' ); // DEBUG: remove in production
			}
		}
	} );
};

wp.domReady( () => {
	if ( 'wsu_help_docs' === select( 'core/editor' ).getCurrentPostType() ) {
		helpDocsUpdateNotices();
	}
} );
