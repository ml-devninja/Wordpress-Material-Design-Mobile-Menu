// Uploading files
var file_frame;

jQuery('.button-upload').live('click', function( event ){

    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( file_frame ) {
        file_frame.open();
        return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
            text: jQuery( this ).data( 'uploader_button_text' ),
        },
        multiple: false  // Set to true to allow multiple files to be selected
    });

    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
        // We set multiple to false so only get one image from the uploader
        attachment = file_frame.state().get('selection').first().toJSON();
        jQuery('.text-upload').val(attachment.url);
        jQuery('.preview-upload').attr('src', attachment.url);
        // Do something with attachment.id and/or attachment.url here
    });

    // Finally, open the modal
    file_frame.open();
});

// Restore the main ID when the add media button is pressed
jQuery('a.add_media').on('click', function() {
    wp.media.model.settings.post.id = wp_media_post_id;
});

(function( $ ) {

    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.color-field').wpColorPicker();
        // console.log('color-picker')
    });

})( jQuery );