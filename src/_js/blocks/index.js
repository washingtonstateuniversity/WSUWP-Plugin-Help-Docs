/**
 * WordPress dependencies
 */
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import * as howto from './howto';

/**
 * Function to register WSUWP Help Docs blocks.
 *
 * @example
 * ```js
 * import { registerHelpDocBlocks } from './blocks';
 *
 * registerHelpDocBlocks();
 * ```
 */
export const registerHelpDocBlocks = () => {
	[
		howto,
	].forEach( ( block ) => {
		if ( ! block ) {
			return;
		}
		const { settings, name } = block;
		registerBlockType( name, settings );
	} );
};
