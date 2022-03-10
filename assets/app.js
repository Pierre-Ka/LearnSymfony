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

/*
    Ecriture du code d'Honoré avec JQuery ( devenu obsolete lors du passage au theme form Bootstrap 5 )
    Ce code à pour but de recuperer le nom de m'image chargée et de l'afficher dans le champ de l'image :
    Sur l'element de classe .custom-file-input j'écoute l'evenement changement (e)
    Une fois que l'on recupere quelque chose, on sort le nom de l'evenement qui a été selectionnée
    (inputFile.files[0].name) et on le met dans le contenu de notre label ('.custom-file-label')
    $('.custom-file-input').on('change', function(e) {
        var inputFile = e.currentTarget;
        $(inputFile).parent().find('.custom-file-label').html(inputFile.files[0].name);
    })
 */