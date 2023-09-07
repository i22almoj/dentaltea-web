$(document).ready(function(){
    $('.toggle-password').each(function(){
        $(this).parent().addClass('toggle-password-group');
        $(this).parent().append('<button class="toggle-password-btn" type="button" aria-label="Mostrar contraseña como texto plano"></button>');
        $(this).attr('type', 'password');
        $(this).attr('aria-label', 'Mostrar contraseña');
    });

    $('.toggle-password-group .toggle-password-btn').click(function(e){
        e.preventDefault();
        var input = $(this).parent().find('.toggle-password');
        if(input.length==0) return false;
        if(input.attr('type')==='password'){
            input.attr('type', 'text');
            input.attr('aria-label', 'Ocultar contraseña');
        }else{
            input.attr('type', 'password');
            input.attr('aria-label', 'Mostrar contraseña');
        }
        
    });
});