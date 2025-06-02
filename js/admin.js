jQuery(document).ready(function($) {
    // Audio Upload Button
    $('#upload_audio_button').click(function(e) {
        e.preventDefault();
        
        var audioUploader = wp.media({
            title: 'Select Audio File',
            button: {
                text: 'Use this audio'
            },
            multiple: false,
            library: {
                type: 'audio'
            }
        });

        audioUploader.on('select', function() {
            var attachment = audioUploader.state().get('selection').first().toJSON();
            $('#podcast_audio').val(attachment.url);
        });

        audioUploader.open();
    });
}); 