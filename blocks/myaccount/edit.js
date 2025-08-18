/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { yourprofileurl, savedeventsurl } = attributes;

	return (
		<>
			<div {...blockProps}>
				{/*
				* This is a visual representation of the buttons in the editor.
				* The actual rendering logic is handled in render.php
				* based on the user's login status.
				*/}
				<div style={{ display: 'flex', gap: '10px' }}>
					<button className="my-account-button">
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_2004_200)">
							<path d="M21.2448 0C21.6958 0.116886 22.2052 0.108537 22.6811 0.16698C30.9734 1.2607 37.8127 7.62263 39.5496 15.7963L39.9922 18.827C39.9588 19.6034 40.034 20.3966 39.9922 21.173C38.9985 38.1215 18.2553 46.2868 6.02144 34.331C-6.36269 22.225 1.57885 1.06032 18.7396 0H21.2365H21.2448ZM18.8816 1.26905C5.0277 2.0956 -3.22282 17.3074 3.75839 29.3968C10.8315 41.6364 28.5434 41.8785 35.9588 29.8393C43.9004 16.9317 33.9296 0.384053 18.8816 1.26905Z" fill="#8D8D8E"/>
							<path d="M19.0392 20.0209C23.1144 19.7287 27.4567 21.3818 29.5027 25.0553C30.3962 26.65 31.749 30.9914 28.8597 31.2503H11.1227C8.85131 30.9079 9.25214 28.5285 9.71978 26.8754C10.8805 22.7593 14.9223 20.3131 19.0392 20.0209ZM19.1144 21.2732C15.3816 21.5487 11.7156 23.7779 10.8221 27.5934C10.6968 28.1361 10.3127 29.7558 10.9974 29.9645H28.9682C29.653 29.7474 29.2772 28.1361 29.1436 27.5934C28.0997 23.1183 23.3983 20.956 19.106 21.2732H19.1144Z" fill="#8D8D8E"/>
							<path d="M19.4319 8.77479C26.2211 8.08183 26.9142 18.2509 20.4256 18.7518C13.9371 19.2528 13.0854 9.42601 19.4319 8.77479ZM19.5071 10.0188C14.3213 10.645 15.8494 18.8353 21.052 17.3409C25.411 16.0885 24.0833 9.46776 19.5071 10.0188Z" fill="#8D8D8E"/>
							</g>
							<defs>
							<clipPath id="clip0_2004_200">
							<rect width="40" height="40" fill="white"/>
							</clipPath>
							</defs>
						</svg>
					</button>
				</div>
				<p style={{ marginTop: '10px', fontSize: '12px', color: '#666' }}>
					{__( 'The buttons above will appear based on user login status on the frontend. This is a preview.', 'bazo' )}
				</p>
			</div>
			<InspectorControls>
				<PanelBody title={ __( 'Display Settings', 'bazo' ) }>
					<TextControl
						label={ __( 'Your Profile URL Slug', 'bazo' ) }
						value={ yourprofileurl }
						onChange={ ( newyourprofileurl ) =>
							setAttributes({ yourprofileurl: newyourprofileurl })
						}
					/>
					<TextControl
						label={ __( 'Saved Events URL Slug', 'bazo' ) }
						value={ savedeventsurl }
						onChange={ ( newsavedeventsurl ) =>
							setAttributes({ savedeventsurl: newsavedeventsurl })
						}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}