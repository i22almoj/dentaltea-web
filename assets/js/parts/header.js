
$(document).ready(function(){  
    var lastScrollTop = 0;
    $('#content-section').scroll(function(e){  
        var st = $(this).scrollTop();
        if (st>100 && st > lastScrollTop){
            $(this).addClass('scrolldown');
        } else {
            $(this).removeClass('scrolldown');
        }
        lastScrollTop = st;
    });
});