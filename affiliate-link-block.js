const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, Placeholder } = wp.components;
const { useSelect, useDispatch } = wp.data;

registerBlockType('affiliate-link/block', {
    title: 'Affiliate Link',
    icon: 'admin-links',
    category: 'common',
    attributes: {
        id: {
            type: 'number',
        },
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const { id } = attributes;

        const affiliateLinks = useSelect(select => select('core').getEntityRecords('postType', 'affiliate_link'));
        const { replaceInnerBlocks } = useDispatch('core/block-editor');

        const options = affiliateLinks ? affiliateLinks.map(link => ({
            label: link.title.rendered,
            value: link.id
        })) : [];

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Affiliate Link Settings">
                        <SelectControl
                            label="Select Affiliate Link"
                            value={id}
                            options={[{ label: 'Select a link', value: '' }, ...options]}
                            onChange={(value) => setAttributes({ id: parseInt(value, 10) })}
                        />
                    </PanelBody>
                </InspectorControls>
                <Placeholder label="Affiliate Link">
                    <SelectControl
                        label="Select Affiliate Link"
                        value={id}
                        options={[{ label: 'Select a link', value: '' }, ...options]}
                        onChange={(value) => setAttributes({ id: parseInt(value, 10) })}
                    />
                </Placeholder>
            </div>
        );
    },
    save: () => null,
});
