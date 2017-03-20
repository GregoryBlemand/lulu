/**********************             Toggle menu responsive                 ************************/
$('.burger').click(function(e){
    $('nav').removeClass('col-sm-3');
    $('nav ul').toggleClass('AffMenu');
});

/**********************             Ajout de champs image                  ************************/
$(document).ready(function() {
    // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
    var $container = $('div#corebundle_galerie_images');

    // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
    var index = $container.find(':input').length;

    // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
    $('#add_image').click(function(e) {
        addImage($container);

        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });

    // On ajoute un premier champ automatiquement s'il n'en existe pas déjà un (cas d'une nouvelle galerie par exemple).
    if (index == 0) {
        addImage($container);
    } else {
        // S'il existe déjà des images, on ajoute un lien de suppression pour chacune d'entre elles
        $container.children('div').each(function() {
            addDeleteLink($(this));
        });
    }

    // La fonction qui ajoute un formulaire ImageType
    function addImage($container) {
        // Dans le contenu de l'attribut « data-prototype », on remplace :
        // - le texte "__name__label__" qu'il contient par le label du champ
        // - le texte "__name__" qu'il contient par le numéro du champ
        var template = $container.attr('data-prototype')
                .replace(/__name__label__/g, 'Image n°' + (index+1))
                .replace(/__name__/g,        index)
            ;

        // On crée un objet jquery qui contient ce template
        var $prototype = $(template);

        // On ajoute un évènement sur nos champs file
        $prototype.find(('input[type="file"]')).change(function(){
            $prototype.append('<img id="myimage'+index+'" style="width: 100%;">');
            var file = $(this).get(0).files[0];
            if (file.type.indexOf("image") == 0) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("myimage"+index).src = e.target.result;
                }
                reader.readAsDataURL(file);
            }

            console.log($(this).get(0).files);
            // Sur le changement d'état, on vérifie combien, il y a de fichier sélectionné
            length = $(this).get(0).files.length;
            // s'il y a plus d'un fichier on ajoute d'autre champ pour les autres
            if(length > 1){
                // on garde le premier fichier pour le champ actuel
                file0 = $(this).get(0).files[0];
                for(i = 1; i < length; i++){ // pour chaque fichier
                    // on ajoute un champ file et on lui assigne un fichier
                    addImage($container);
                    console.log($(this).get(0).files[i]);
                    $('input[type=file]')[$('input[type=file]').length - 1].files[0] = $(this).get(0).files[i];
                }

                // on rend son fichier au champ actuel
                //$(this).get(0).files;

            }
        });

        // On ajoute au prototype un lien pour pouvoir supprimer l'image
        addDeleteLink($prototype);

        // On ajoute le prototype modifié à la fin de la balise <div>
        $container.append($prototype);

        // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
        index++;
    }

    // La fonction qui ajoute un lien de suppression d'une image
    function addDeleteLink($prototype) {
        // Création du lien
        var $deleteLink = $('<p><a href="#" class="btn btn-danger">Supprimer</a><br></p>');

        // Ajout du lien
        $prototype.append($deleteLink);

        // Ajout du listener sur le clic du lien pour effectivement supprimer l'image
        $deleteLink.click(function(e) {
            $prototype.remove();

            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });
    }
});