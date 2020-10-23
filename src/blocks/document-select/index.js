/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import { file as icon } from './icons';

const { name, attributes, supports } = metadata;

export { metadata, name };

export const settings = {
	title: __( 'Document Select' ),
	description: __( 'Select or upload a file.' ),
	icon,
	keywords: [ __( 'document' ), __( 'pdf' ), __( 'upload' ) ],
	attributes,
	supports,
	edit,
};
