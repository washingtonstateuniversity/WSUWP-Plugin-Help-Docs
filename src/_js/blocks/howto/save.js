/**
 * WordPress dependencies
 */
const {	RichText } = wp.editor;

export default function save( { attributes } ) {
	const { values } = attributes;
	const tagName = 'ol';

	return (
		<RichText.Content tagName={ tagName } value={ values } multiline="li" />
	);
}
