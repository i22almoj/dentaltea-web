$(document).ready(function($){
    $('#sidebar-open').click(function(e){
        e.preventDefault();
        $('aside#sidebar').removeClass('collapsed');
    });
    $('#sidebar-toggle').click(function(e){
        e.preventDefault();
        $('aside#sidebar').toggleClass('collapsed');
    });

    $('#menu-toggle').click(function(e){
        e.preventDefault();
        $('aside#sidebar').addClass('open');
    });

    $('#menu-close').click(function(e){
        e.preventDefault();
        $('aside#sidebar').removeClass('open');
    });

    $('body').click(function(e){
        if (window.innerWidth<=990 && 
            !$(e.target).is($('aside#sidebar')) && !$('aside#sidebar').has(e.target).length &&
            !$(e.target).is($('#menu-toggle')) && !$('#menu-toggle').has(e.target).length
        ) {
            $('aside#sidebar').removeClass('open');
        }
    });
});

