import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, CheckboxControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import './editor.scss';

export default function Edit(props) {
    const { attributes, setAttributes } = props;
    const { postsToShow, selectedCategories, showLoadMoreButton, postType, taxonomy } = attributes;
    const blockProps = useBlockProps();

    // Fetch available post types dynamically from WordPress
    const postTypes = useSelect(select => {
        const types = select('core').getPostTypes({ per_page: -1 });
        // Filter out unwanted post types (optional)
        return types
            ? types
                .filter(type => !type.viewable || type.slug === 'attachment' ? false : true)
                .map(type => ({
                    label: type.labels.singular_name,
                    value: type.slug
                }))
            : [];
    }, []);

    // Dynamically fetch taxonomy for selected post type
    const taxonomies = useSelect(
        select => {
            if (!postType) return [];
            const postTypeObj = select('core').getPostType(postType);
            if (!postTypeObj || !postTypeObj.taxonomies) return [];
            return postTypeObj.taxonomies.map(tax => {
                const taxonomyObj = select('core').getTaxonomy(tax);
                return taxonomyObj
                    ? { label: taxonomyObj.labels.singular_name, value: taxonomyObj.slug }
                    : null;
            }).filter(Boolean);
        },
        [postType]
    );

    // Only fetch categories for selected taxonomy
    const filteredCategories = useSelect(
        select => {
            if (!taxonomy) return [];
            return select('core').getEntityRecords('taxonomy', taxonomy, { per_page: -1 }) || [];
        },
        [taxonomy]
    );

    // Handle category change, using category.id for selectedCategories
    const handleCategoryChange = (id, isChecked) => {
        if (isChecked) {
            setAttributes({ selectedCategories: [...selectedCategories, id] });
        } else {
            setAttributes({ selectedCategories: selectedCategories.filter(termId => termId !== id) });
        }
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'bazo')}>
                    <SelectControl
                        label={__('Post Type', 'bazo')}
                        value={postType}
                        options={postTypes}
                        onChange={(value) => {
                            setAttributes({ postType: value, taxonomy: '' });
                        }}
                    />
                    {taxonomies.length > 0 && (
                        <SelectControl
                            label={__('Category Type', 'bazo')}
                            value={taxonomy}
                            options={taxonomies}
                            onChange={(value) => setAttributes({ taxonomy: value, selectedCategories: [] })}
                        />
                    )}
                    <RangeControl
                        label={__('Posts to show', 'bazo')}
                        value={postsToShow}
                        onChange={(value) => setAttributes({ postsToShow: value })}
                        min={1}
                        max={20}
                    />
                    <ToggleControl
                        label={__('Show Load More Button', 'bazo')}
                        checked={showLoadMoreButton}
                        onChange={() => setAttributes({ showLoadMoreButton: !showLoadMoreButton })}
                    />
                </PanelBody>
                {taxonomy && (
                    <PanelBody title={__('Filter by Category', 'bazo')}>
                        <CheckboxControl
                            label={__('All', 'bazo')}
                            checked={selectedCategories.length === 0}
                            onChange={(isChecked) => isChecked && setAttributes({ selectedCategories: [] })}
                        />
                        {filteredCategories && filteredCategories.map(category => (
                            <CheckboxControl
                                key={category.id}
                                label={category.name}
                                checked={selectedCategories.includes(category.id)} // Check against category.id
                                onChange={(isChecked) => handleCategoryChange(category.id, isChecked)} // Pass category.id
                            />
                        ))}
                    </PanelBody>
                )}
            </InspectorControls>
            <div {...blockProps}>
                <ServerSideRender
                    block="bazo/event-grid"
                    attributes={attributes}
                />
            </div>
        </>
    );
}
