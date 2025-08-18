/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

// Import our block's metadata and styling.
import metadata from './block.json';
import Edit from './edit';
import './style.scss'; // This will be compiled to style.css for the front end
import './editor.scss'; // This will be compiled to editor.css for the editor only

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null, // Dynamic blocks must return null for the save function.
} );
