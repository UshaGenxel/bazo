import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import './style.scss';

registerBlockType('bazo/review-section', {
    edit: function() {
        const blockProps = useBlockProps();
        
        return (
            <div { ...blockProps }>
                <div className="bazo-review-section">
                    <div className="bazo-review-header">
                        <h3 className="bazo-review-title">
                            <svg className="bazo-review-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            review
                        </h3>
                        <div className="bazo-review-actions">
                            <button className="bazo-review-action" title="Would definitely visit again">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M14 9V5a3 3 0 0 0-6 0v4"></path>
                                    <rect x="2" y="9" width="20" height="10" rx="2" ry="2"></rect>
                                    <path d="M16 11h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2"></path>
                                </svg>
                            </button>
                            <button className="bazo-review-action" title="Would probably not visit again">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M10 15v4a3 3 0 0 0 6 0v-4"></path>
                                    <rect x="2" y="9" width="20" height="10" rx="2" ry="2"></rect>
                                    <path d="M8 13h-2a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h2"></path>
                                </svg>
                            </button>
                            <button className="bazo-review-action" title="Add a note">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    },
    
    save: function() {
        return null; // Use server-side rendering
    }
});
