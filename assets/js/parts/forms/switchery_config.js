import Switchery from './switchery';

//Switchery
$(document).ready(function() {
    if($('[data-toggle="switchery"]').length>0){
        $('[data-toggle="switchery"]').each(function (idx, obj) {
            new Switchery($(this)[0], $(this).data());
        });
    }
});