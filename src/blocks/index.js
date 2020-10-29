/**
 * WordPress dependencies
 */
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import * as documentsList from './documents-list';
import * as documentSelect from './document-select';

/**
 * Function to register plugin blocks.
 *
 * @example
 * ```js
 * import { registerBlocks } from './blocks';
 *
 * registerBlocks();
 * ```
 */
export const registerBlocks = () => {
	[ documentsList, documentSelect ].forEach( ( block ) => {
		if ( ! block ) {
			return;
		}
		const { settings, name } = block;
		registerBlockType( name, settings );
	} );
};
