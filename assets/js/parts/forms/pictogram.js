$(document).ready(function(){
    
    if( $('#pictogram_change_image').length>0&&$('#form-wrap #pictogram .dropify-clear').length>0){
        $('#form-wrap #pictogram .dropify-clear').click(function(e){
            $('#pictogram_change_image').val(1);
        });

        $('#pictogram_submit').click(function(e){
            if(!$('#form-wrap #pictogram .dropify-wrapper').hasClass('has-preview')){
                e.preventDefault();
                $('.pictogram-image-group').append('<div class="invalid-feedback d-block">Debes seleccionar una imagen.</div>');
            }
        });

        $('.pictogram-image-group .dropify-wrapper').click(function(){
            $('.pictogram-image-group .invalid-feedback').remove();
        })
    }


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
    }
});