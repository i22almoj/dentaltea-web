import './forms/dropify';
import './forms/select2';
import Switchery from './forms/switchery';
import './forms/switchery_config';
import './forms/toggle-password';
import './forms/pictogram';
import './forms/quill';
import './forms/sortable';
import './forms/date';
import './forms/calendar';

$('#form-wrap .btn-save-bottom').click(function(e){
    e.preventDefault;

    $('#form-wrap form button[type="submit"]').trigger('click');
})