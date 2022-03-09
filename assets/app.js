/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

/*
    Si on avait d'autres fichiers .css ou .scss ou .sass, il faut les déclarer ici
 */

// start the Stimulus application
import './bootstrap';

/*
    Suite à l'importation de jS dans Webpack ( yarn add jquery @popperjs/core --dev ),
    on importe nos JS ici. L'ancienne synthaxe utilisait un require mais maintenant on va utiliser
     un import comme dans le tuto Honoré:
 */

import $ from 'jquery';
import 'bootstrap';