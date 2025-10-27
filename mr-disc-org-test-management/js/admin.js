jQuery(document).ready(function($) {
    // Media uploader
    var mediaUploader;
    $('.upload-logo, .upload-bg-image').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var inputField = button.prev();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            inputField.val(attachment.url);
        });
        mediaUploader.open();
    });

    // Color picker
    $('.color-picker').wpColorPicker();
});
