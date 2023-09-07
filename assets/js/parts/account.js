//Register roles

$(document).ready(function() {
    //Register
    if($('select#register_role').length>0){   
        if($('select#register_role').val()!=''){
            $('body').attr('class', 'account');
            $('body').addClass('form_'+$('select#register_role').val());
        }
        $('select#register_role').change(function(){
            $('body').attr('class', 'account');
            $('body').addClass('form_'+$('select#register_role').val());
        });
    }
    
    //New User
    if($('select#user_new_role').length>0){   
        if($('select#user_new_role').val()!=''){
            $('body').attr('class', '');
            $('body').addClass('form_'+$('select#user_new_role').val());
        }
        $('select#user_new_role').change(function(){
            $('body').attr('class', '');
            $('body').addClass('form_'+$('select#user_new_role').val());
        });
    }
});