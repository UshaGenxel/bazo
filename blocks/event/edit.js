import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, CheckboxControl, SelectControl, TextControl, Button, ButtonGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import './editor.scss';

export default function Edit(props) {
    const { attributes, setAttributes } = props;
    const { postsToShow, selectedCategories, showLoadMoreButton, postType, taxonomy, showLoader, showAds, adPositions } = attributes;
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

    // Handle ad position changes
    const updateAdPosition = (index, field, value) => {
        const newAdPositions = [...adPositions];
        newAdPositions[index] = { ...newAdPositions[index], [field]: value };
        setAttributes({ adPositions: newAdPositions });
    };

    const addAdPosition = () => {
        const newAdPositions = [...adPositions, { position: 1, span: 2, text: 'AD Video' }];
        setAttributes({ adPositions: newAdPositions });
    };

    const removeAdPosition = (index) => {
        const newAdPositions = adPositions.filter((_, i) => i !== index);
        setAttributes({ adPositions: newAdPositions });
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
                    <ToggleControl
                        label={__('Show Loader', 'bazo')}
                        checked={showLoader}
                        onChange={() => setAttributes({ showLoader: !showLoader })}
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
                                checked={selectedCategories.includes(category.id)}
                                onChange={(isChecked) => handleCategoryChange(category.id, isChecked)}
                            />
                        ))}
                    </PanelBody>
                )}
                <PanelBody title={__('Google Ad Settings', 'bazo')} initialOpen={false}>
                    <ToggleControl
                        label={__('Show Ad Blocks', 'bazo')}
                        checked={showAds}
                        onChange={() => setAttributes({ showAds: !showAds })}
                    />
                    {showAds && (
                        <>
                            <p>{__('Configure ad positions in the event grid:', 'bazo')}</p>
                            {adPositions.map((ad, index) => (
                                <div key={index} style={{ border: '1px solid #ddd', padding: '12px', marginBottom: '12px', borderRadius: '4px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                        <strong>{__('Ad', 'bazo')} #{index + 1}</strong>
                                        <Button
                                            isDestructive
                                            isSmall
                                            onClick={() => removeAdPosition(index)}
                                        >
                                            {__('Remove', 'bazo')}
                                        </Button>
                                    </div>
                                    <RangeControl
                                        label={__('Position (after post number)', 'bazo')}
                                        value={ad.position}
                                        onChange={(value) => updateAdPosition(index, 'position', value)}
                                        min={1}
                                        max={postsToShow}
                                    />
                                    <SelectControl
                                        label={__('Span Columns', 'bazo')}
                                        value={ad.span}
                                        options={[
                                            { label: '1 Column', value: 1 },
                                            { label: '2 Columns', value: 2 },
                                            { label: '3 Columns', value: 3 },
                                            { label: '4 Columns', value: 4 }
                                        ]}
                                        onChange={(value) => updateAdPosition(index, 'span', parseInt(value))}
                                    />
                                    <TextControl
                                        label={__('Ad Text', 'bazo')}
                                        value={ad.text}
                                        onChange={(value) => updateAdPosition(index, 'text', value)}
                                    />
                                </div>
                            ))}
                            <Button
                                isPrimary
                                isSmall
                                onClick={addAdPosition}
                                style={{ width: '100%' }}
                            >
                                {__('Add Ad Position', 'bazo')}
                            </Button>
                        </>
                    )}
                </PanelBody>
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