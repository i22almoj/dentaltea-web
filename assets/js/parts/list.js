import { Modal } from 'bootstrap';
$(document).ready(function(){
    if($('.list-toolbar form').length>0){
        $('#filter .filter-field select').change(function(){
                $('.list-toolbar form').submit();
            });
    

        if($('.pagination-wrap .pagination .page-item').length>0){
            $('.pagination-wrap .pagination .page-item a').click(function(){
                $('.list-toolbar #current_page').val($(this).attr('page'));
                $('.list-toolbar form').submit();
            });
        
        }

        $('.list-toolbar form').submit(function() {
            $(this).find(":input, select").filter(function(){ return !this.value; }).attr("disabled", "disabled");
            return true; // ensure form still submits
        });

        $('.list-toolbar .filter-clean').click(function(){
            $('.list-toolbar .filter-field select').val('');
            $('.list-toolbar #search').val('');
            $('.list-toolbar #current_page').val('1');
            $('.list-toolbar form').submit();
        });

        $('.list-toolbar #p_size').change(function(){
            $('.list-toolbar form').submit();
        });

        $('.list-table th.sortable').append('<span class="sort"></span>');

        $('.list-toolbar #orderby').change(function(){
            $('.list-toolbar form').submit();
        });

        $('.list-toolbar .orderby-field .sort').click(function(){ 
            if($(this).hasClass('sort-ASC'))   $('#current-order').val('DESC');
            else   $('#current-order').val('ASC');


            $('.list-toolbar form').submit();
        });

        $('.list-toolbar .view-field .view-table-btn').click(function(){
            $('#current-view').val('table');
            $('.list-toolbar form').submit();
        });

        $('.list-toolbar .view-field .view-grid-btn').click(function(){
            $('#current-view').val('grid');
            $('.list-toolbar form').submit();
        });
        
        
        $('.list-table th.sortable').click(function(){ 
            if($(this).hasClass('asc'))
                $('#current-order').val('DESC');
            else
                $('#current-order').val('ASC');
          
            $('#orderby').val($(this).attr('orderby')).trigger('change');
            

            $('.list-toolbar form').submit();
        });

        $('.list-toolbar #search').keyup(function(e){
            $(this).val($(this).val().replace('"', '').replace("'", ""));
        });

        $('.list-toolbar #search').keydown(function(e){
            if(e.keyCode==13){
                e.preventDefault();
                $('.list-toolbar form').submit();
            }
        });

        $('.show-toolbar-btn').click(function(){
            if($('.list-toolbar').hasClass('open')){
                $('.list-toolbar').removeClass('open');
                $(this).find('span').attr('class', 'options-hide');
            }else{
                $('.list-toolbar').addClass('open');
                $(this).find('span').attr('class', 'options-show');
            }
        });

        $('.list-toolbar form').find( ":input" ).prop( "disabled", false );

        if($('.modal-pictogram').length>0){
            $('.view-modal').click(function(e){ 
                e.preventDefault();
                let id = $(this).attr('id').replace('view-modal-', '');
                $('#modal-'+id).modal();
                var myModal = new Modal(document.getElementById('modal-'+id), {});
                myModal.show();

                $('.modal-header .close').click(function(){
                    myModal.hide();
                });
                
                $(window).resize(function(){
                    if(window.innerWidth <= 767){
                        myModal.hide();
                    }
                })
                
            });
        }
    }
});

