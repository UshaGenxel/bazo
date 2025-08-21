import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import './style.scss';

registerBlockType('bazo/event-meta', {
    edit: function() {
        const blockProps = useBlockProps();
        
        return (
            <div { ...blockProps }>
                <div className="bazo-event-meta-card">
                    <div className="bazo-event-meta-list">
                        <div className="bazo-event-meta-item">
                            <svg className="bazo-event-meta-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span className="bazo-event-meta-value">Event Date</span>
                        </div>
                        <div className="bazo-event-meta-item">
                            <svg className="bazo-event-meta-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span className="bazo-event-meta-value">Event Time</span>
                        </div>
                        <div className="bazo-event-meta-item">
                            <svg className="bazo-event-meta-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                                <path d="M12 6v2m0 8v2"></path>
                            </svg>
                            <span className="bazo-event-meta-value">Price</span>
                        </div>
                    </div>
                    <div className="bazo-event-meta-actions">
                        <a className="bazo-event-meta-action">direction</a>
                        <a className="bazo-event-meta-action">web</a>
                        <a className="bazo-event-meta-action">ticket</a>
                        <a className="bazo-event-meta-action">instagram</a>
                        <a className="bazo-event-meta-action">email</a>
                    </div>
                </div>
            </div>
        );
    },
    
    save: function() {
        return null; // Use server-side rendering
    }
});
