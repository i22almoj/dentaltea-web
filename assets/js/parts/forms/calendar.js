import { Calendar } from 'fullcalendar';
import { Modal } from 'bootstrap';

if($('#calendarDates').length>0){
    document.addEventListener('DOMContentLoaded', function() {
        var calendar = new Calendar(document.getElementById('calendarDates'), {
            initialView: 'dayGridMonth',
            locale: 'es',
            'firstDay': 1,
            buttonText: {
                today: 'Hoy'
            },
            events: userDates,
            eventClick: function(event, jsEvent, view) {
                if(event.event === undefined || event.event.id === undefined) return false;

                const eventId = event.event.id;
                var myModal = new Modal(document.getElementById('modal-date-'+eventId), {});
                myModal.show();
                $('.modal-header .close').click(function(){
                    myModal.hide();
                });
                
                console.log(JSON.stringify(event.event));
            }
        })
        calendar.render();
    });
}