/**
 * WordPress dependencies
 */
const {	__ } = wp.i18n;

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import save from './save';

const { name } = metadata;

export { metadata, name };

const supports = {
	align: [ 'wide', 'full' ],
};

export const settings = {
	title: __( 'How To' ),
	description: __( 'Display an instructional list.' ),
	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="468 268 24 24"><path fill="none" d="M468 268h24v24h-24v-24z" /><path d="M472 272h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-16a2 2 0 0 1-2-2v-12c0-1.1.9-2 2-2zm0 2v12h10v-12h-10zm12 0v12h4v-12h-4z" /></svg>,
	keywords: [ __( 'ordered list' ), __( 'instructions' ), __( 'numbered list' ) ],
	supports,
	edit,
	save,
};
