jQuery(document).ready(function($) {
    var file_frame;

    $('#upload_image_button').on('click', function(event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select or Upload Image',
            button: {
                text: 'Use this image',
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#affiliate_link_image_id').val(attachment.id);
            $('#affiliate_link_image_preview').html('<img src="' + attachment.url + '" style="max-width: 100%;" />');
        });

        // Finally, open the modal.
        file_frame.open();
    });

    $('#copy_shortcode_button').on('click', function() {
        var id = $('#affiliate_link_select').val();
        if (id) {
            var shortcode = '[affiliate_link id="' + id + '"]';
            navigator.clipboard.writeText(shortcode).then(function() {
                alert('Shortcode copied to clipboard: ' + shortcode);
            }, function(err) {
                alert('Failed to copy shortcode: ', err);
            });
        } else {
            alert('Please select an affiliate link.');
        }
    });
});
