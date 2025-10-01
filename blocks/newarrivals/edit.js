import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const { postsToShow, postType } = attributes;
    const blockProps = useBlockProps();

    // Dynamically fetch all publicly queryable post types
    const postTypes = useSelect(select => {
        const postTypes = select('core').getPostTypes({ per_page: -1 });
        if (!postTypes) return [];
        return postTypes
            .filter(type => type.viewable && type.rest_base !== 'media')
            .map(type => ({
                label: type.labels.name,
                value: type.slug,
            }));
    }, []);

    // Removed event type options

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'bazo')}>
                    <SelectControl
                        label={__('Post Type', 'bazo')}
                        value={postType}
                        options={postTypes}
                        onChange={(value) => {
                            setAttributes({ postType: value });
                        }}
                    />
                    {/* Removed Event Type control */}
                    <RangeControl
                        label={__('Items to show', 'bazo')}
                        value={postsToShow}
                        onChange={(value) => setAttributes({ postsToShow: value })}
                        min={1}
                        max={12}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <ServerSideRender
                    block="bazo/newarrivals"
                    attributes={attributes}
                />
            </div>
        </>
    );
}