/**
 * WordPress dependencies
 */
const {	__ } = wp.i18n;
const { createBlock } = wp.blocks;
const {	RichText } = wp.editor;

export default function HowToEdit( {
	attributes,
	insertBlocksAfter,
	setAttributes,
	mergeBlocks,
	onReplace,
	className,
} ) {
	const { values } = attributes;

	return (
		<RichText
			identifier="values"
			multiline="li"
			tagName="ol"
			onChange={ ( nextValues ) => setAttributes( { values: nextValues } ) }
			value={ values }
			wrapperClassName="howto-list"
			className={ className }
			placeholder={ __( 'Write instructions…' ) }
			onMerge={ mergeBlocks }
			unstableOnSplit={
				insertBlocksAfter ?
					( before, after, ...blocks ) => {
						if ( ! blocks.length ) {
							blocks.push( createBlock( 'core/paragraph' ) );
						}

						if ( after !== '<li></li>' ) {
							blocks.push( createBlock( 'core/list', {
								values: after,
							} ) );
						}

						setAttributes( { values: before } );
						insertBlocksAfter( blocks );
					} :
					undefined
			}
			onRemove={ () => onReplace( [] ) }
		/>
	);
}
