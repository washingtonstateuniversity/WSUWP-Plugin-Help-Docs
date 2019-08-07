/**
 * WordPress dependencies
 */
const { registerBlockStyle } = wp.blocks;

const addBlockStyles = () => {
	registerBlockStyle( 'core/list', {
		name: 'step-list',
		label: 'Steps',
	} );
};

const init = () => {
	addBlockStyles();
};

export { init };
