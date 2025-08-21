import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

registerBlockType('bazo/product-header', {
    edit: function({ attributes, setAttributes }) {
        const { showShare, showWishlist, showNavigation } = attributes;
        const blockProps = useBlockProps();
        
        return (
            <div { ...blockProps }>
                <div className="bazo-product-header-preview">
                    <div className="bazo-product-header-content">
                        <div className="bazo-product-header-back">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                            </svg>
                            <span>Back</span>
                        </div>
                        
                        <h1 className="bazo-product-header-title">Product Title</h1>
                        
                        <div className="bazo-product-header-actions">
                            {showShare && (
                                <button className="bazo-product-header-action" title="Share Product">
                                    <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_42_1807)">
                                        <path d="M17.5003 24.9997C17.1954 24.9997 16.913 24.8317 16.7662 24.5573L10.7125 13.0904L0.417955 7.3009C0.124308 7.13853 -0.0394564 6.80818 0.00572002 6.47783C0.0508965 6.14749 0.29372 5.87313 0.621249 5.78914L22.9667 0.0276655C23.249 -0.0451228 23.5483 0.0332646 23.7572 0.234832C23.9662 0.4364 24.0509 0.733153 23.9831 1.01311L18.3135 24.3726C18.2344 24.7085 17.9521 24.9549 17.6076 24.9941C17.5737 24.9941 17.5398 24.9997 17.506 24.9997H17.5003ZM3.0156 6.86977L11.746 11.7858C11.8871 11.8642 12.0001 11.9818 12.0735 12.1217L17.218 21.8586L22.0405 1.96496L3.0156 6.86977Z" fill="#8D8D8E"/>
                                        <path d="M11.3392 13.3198C11.1246 13.3198 10.9157 13.2415 10.7519 13.0791C10.43 12.7599 10.43 12.2392 10.7519 11.9145L22.5881 0.240338C22.91 -0.0788107 23.4352 -0.0788107 23.7627 0.240338C24.0846 0.559487 24.0846 1.0802 23.7627 1.40495L11.9265 13.0847C11.7627 13.2471 11.5538 13.3254 11.3392 13.3254V13.3198Z" fill="#8D8D8E"/>
                                        </g>
                                        <defs>
                                        <clipPath id="clip0_42_1807">
                                        <rect width="24" height="25" fill="white"/>
                                        </clipPath>
                                        </defs>
                                    </svg>
                                </button>
                            )}
                            
                            {showWishlist && (
                                <button className="bazo-product-header-action" title="Add to Wishlist">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/>
                                    </svg>
                                </button>
                            )}
                            
                            {showNavigation && (
                                <div className="bazo-product-header-navigation">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                                    </svg>
                                    <div className="bazo-product-header-divider"></div>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                    </svg>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                
                <InspectorControls>
                    <PanelBody title="Display Options">
                        <ToggleControl
                            label="Show Share Button"
                            checked={showShare}
                            onChange={(value) => setAttributes({ showShare: value })}
                        />
                        <ToggleControl
                            label="Show Wishlist Button"
                            checked={showWishlist}
                            onChange={(value) => setAttributes({ showWishlist: value })}
                        />
                        <ToggleControl
                            label="Show Navigation Controls"
                            checked={showNavigation}
                            onChange={(value) => setAttributes({ showNavigation: value })}
                        />
                    </PanelBody>
                </InspectorControls>
            </div>
        );
    },
    
    save: function() {
        return null; // Use server-side rendering
    }
});
