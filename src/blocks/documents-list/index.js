/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import { icon } from './icons';

const { name, category, supports } = metadata;

export { name };

export const settings = {
	title: __( 'Documents List' ),
	icon,
	category,
	description: __( 'Display a list of documents.' ),
	keywords: [ __( 'media' ), __( 'documents' ) ],
	supports,
	example: {},
	edit,
};
