import Sortable, { MultiDrag, Swap } from 'sortablejs';

$(document).ready(function(){
    if($('.items-sort .row').length>0){
        initSortable();
        initAddPictogram();
        initDeletePictogram();
    }
});

function initSortable(){
    sortItemsUpdate();

    var sortable = Sortable.create(document.querySelector('.items-sort .row'), {
        handle: '.handle', // handle's class
        animation: 500,
        ghostClass: 'ghost-item',

        onSort: function(evt){
            sortItemsUpdate();
        }
    });

    if($('.items-sort .row .item textarea').length>0){
        $('.items-sort .row .item textarea').each(function(){ console.log();
            $(this).css('height', '30px');
            $(this).css('height', ($(this).get(0).scrollHeight+5)+"px");
        });

        $('.items-sort .row .item textarea').keyup(function(){  
            $(this).css('height', '30px');
            $(this).css('height', ($(this).get(0).scrollHeight+5)+"px");
            sortItemsUpdate();
        });
    }
}



function sortItemsUpdate(){
    
    let items = [];
    if($('.items-sort .row .item').length>0){
        let i = 0;
        $('.items-sort .row .item').each(function() {
            i++;
            let item_id = parseInt($(this).attr('item_id'));
            let item = { 'id': item_id, 'description' : $(this).find('textarea').val(), 'sort_number' : i };
            items.push(item);
        }); 
        
        $('.sequence_pictograms_hidden_input').val(JSON.stringify(items));
    }else{
        $('.sequence_pictograms_hidden_input').val('');
    }
}

function initDeletePictogram(){
    $('.items-sort .row .item .delete').click(function(){
        $(this).parent().remove();
        sortItemsUpdate();
    });
}

function initAddPictogram(){
    $('#add-pictogram-list .pictogram-item').click(function(){ 
        
        let item = { 'id': parseInt($(this).attr('item_id')), 'description': $(this).find('p.pictogram-description').html(), 'image': $(this).find('img').attr('src') }
        let html_item = `
            <div class="col-lg-2 item p-3 text-center" draggable="false" item_id="${item.id}">
                <div class="delete"><span class="material-icons">cancel</span></div>
                <div class="content">
                    <img src="${item.image}"  draggable="false" style="max-width: 100%;"/> <br />
                    <textarea style="height: auto;">${item.description}</textarea>
                </div>
                <div class="handle"><i class="fas fa-arrows-alt"></i></div>
            </div>
        `;
        $('#sequence-pictograms-list .row').append(html_item);
        initSortable();
        sortItemsUpdate();
        initDeletePictogram();
        setTimeout(() => { sortItemsUpdate(); }, 500)
        $('#modal-add-pictogram .btn-close').trigger('click');
    });
}


