import { Modal } from 'bootstrap';

$(document).ready(function(){ 
    if($(".input_date_author_id").length>0){
        userUsequences();

        $(".input_date_author_id").change(function(){
            userUsequences();
        });
    }
    
    if($('.modal-select-sequence').length>0){
        $('.view-modal, #selected-sequence .sequence-thumb, #selected-sequence .sequence-description').click(function(e){ 
            e.preventDefault();
            
            //$('#modal-select-sequence').modal();
            var myModal = new Modal(document.getElementById('modal-select-sequence'), {});
            myModal.show();

            $('.modal-header .close').click(function(){
                myModal.hide();
            });
            
            $(window).resize(function(){
                if(window.innerWidth <= 767){
                    myModal.hide();
                }
            });
            
        });

        
    }
});

function userUsequences(){ 

    let token = $('#_token').val(); 
    let user_id = $(".input_date_author_id").val();
    
    $('#selected-sequence .sequence-action .delete').click(function(){
        emptyUserSequence();
    });

    $('#date_form_author').change(function(){
        emptyUserSequence();
    });

    $.ajax({
        url: base_url+'admin/ajax/user-sequences',
        method: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: { "user_id": user_id },
        success: function(response) {
            $('#modal-select-sequence .modal-body').html(response);
            
            $('#select-sequence .item-sequence').click(function(e){
                e.preventDefault();
                const id = parseInt($(this).attr('item_id'));
                const thumb = $(this).find('.sequence-thumb').html();
                const description = $(this).find('.sequence-description').html();
                $('#selected-sequence .sequence-thumb').html(thumb);
                $('#selected-sequence .sequence-description').html('<strong>'+description+'</strong>');
                $('#modal-select-sequence .btn-close').trigger('click');
                $('#selected-sequence .sequence-action .delete').removeClass('d-none');
                $('.input_date_sequence_id').val(id);
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("Error al realizar la llamada AJAX: " + textStatus + ", " + errorThrown);
        }
    });
}

function emptyUserSequence(){
    $('#selected-sequence .sequence-thumb').html('');
    $('#selected-sequence .sequence-description').html('No seleccionado');
    $('#selected-sequence .sequence-action .delete').addClass('d-none');
    $('.input_date_sequence_id').val('');
}
