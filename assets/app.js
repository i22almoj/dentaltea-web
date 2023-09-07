/******Vendor scripts **********/

//jQuery
var $ = require('jquery');
global.$ = global.jQuery = $;

//Bootstrap
import popper from 'popper.js';
global.popper = global.Popper = popper;
import 'bootstrap';

//Iconos FontAwesome
import '@fortawesome/fontawesome-free/js/all.js';

//Iconos Material Icons
import 'material-icons/iconfont/material-icons.scss';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.snow.css';

//Dropify
import 'dropify';

//Select 2
import 'select2';

//App scripts index
import './js/index';

//App styles index
import './scss/index.scss';
