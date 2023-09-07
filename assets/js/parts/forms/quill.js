import Quill from 'quill';


$(document).ready(function(){
    if($('.quill-wrapper').length>0){
        let i = 0;
        $('.quill-wrapper').each(function(){ i++; 
            let textarea = $(this).find('textarea');
            $(this).append('<div id="quill-editor-'+i+'">'+textarea.val()+'</div>');
            textarea.addClass('d-none');

        });
        var quill = new Quill('#quill-editor-'+i, {
            theme: 'snow', // Establece el tema de Quill. Puede ser 'snow' o 'bubble'.
            modules: {
                'toolbar': [[{ 'header': [false, 1, 2, 3] }], ['bold', 'italic', 'underline'], [ { 'align': [] }],[{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link'], ['clean']]
                
            }
        });

        $('.ql-editor').keyup(function(e){
             let i = 0;
            $('.quill-wrapper').each(function(){ i++;
                let html = $('#quill-editor-'+i+' .ql-editor').html();
                let textarea = $('#quill-editor-'+i).parent().find('textarea');
                textarea.html(html);
            });
            //$(this).submit();

        });
        $('.quill-wrapper').click(function(e){
            let i = 0;
           $('.quill-wrapper').each(function(){ i++;
               let html = $('#quill-editor-'+i+' .ql-editor').html();
               let textarea = $('#quill-editor-'+i).parent().find('textarea');
               textarea.html(html);
            
           });

       });
    }
});