/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

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
export default function Edit() {
    const blockProps = useBlockProps();
    return (
        <div {...blockProps}>
            <div className="profile-picture">
                <img src="https://placehold.co/128x128/e2e8f0/6b7280?text=Profile" alt="Placeholder Profile Picture" />
                <div className="edit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-camera">
                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                </div>
            </div>
            <form>
                <input type="text" className="input-field" placeholder="First Name" value="John" disabled />
                <input type="text" className="input-field" placeholder="Last Name" value="Doe" disabled />
                <input type="email" className="input-field" placeholder="Email" value="john.doe@example.com" disabled />
                <input type="text" className="input-field" placeholder="Biographical Info" value="About me..." disabled />
                <input type="password" className="input-field" placeholder="Password" disabled />
                <select className="input-field" disabled>
                    <option value="" disabled>Country of residence</option>
                    <option value="us" selected>United States</option>
                </select>
                <select className="input-field" disabled>
                    <option value="" disabled>City</option>
                    <option value="ny" selected>New York</option>
                </select>
                <input type="tel" className="input-field" placeholder="Phone" value="+1 234 567 890" disabled />
                <button type="submit" className="update-button" disabled>Update Profile</button>
            </form>
        </div>
    );
}