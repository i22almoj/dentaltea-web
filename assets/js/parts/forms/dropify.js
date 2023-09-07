//Dropify

$(document).ready(function() {
    if($('.dropify').length>0){
        $('.dropify').dropify({
            messages: {
                'default': 'Arrastra un archivo o haz clic',
                'replace': 'Arrastra un archivo o haz clic para reemplazar',
                'remove': 'Eliminar',
                'error': 'Ha habido un error en la subida.'
            },
            error: {
                'fileSize': 'El archivo es demasiado grande (4 Mb máximo).',
                'fileExtension': 'Solo se permiten archivos de imagenes ({{ value }}).'
            }
        });

        if(jQuery('#advice_delete_image').length>0){
            jQuery('.advice-image-group .dropify-clear').click(function(e){
                jQuery('#advice_delete_image').val(1);
            });
        }
    }
});