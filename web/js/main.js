/**********************             Toggle menu responsive                 ************************/
$('.burger').click(function(e){
    $('nav').removeClass('col-sm-3');
    $('nav ul').toggleClass('AffMenu');
});

$(document).ready(function() {
    /**********************             Ajout de champs image dans les formulaires de galerie                  ************************/
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
    if (index == 0 && $container.length > 0) {
        // addImage($container); finalement je ne l'ajoute pas...
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
        var $deleteLink = $('<p><a href="#" class="btn btn-danger" id="suppr">Supprimer</a><br></p>');

        // Ajout du lien
        $prototype.append($deleteLink);

        // Ajout du listener sur le clic du lien pour effectivement supprimer l'image
        $deleteLink.click(function(e) {
            $prototype.remove();

            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });
    }

    /**********************             Gestion de champs image pour l'edition des galeries               ************************/
    var nbimg = $('form img').length;

    for(var i=0; i < nbimg; i++){
        $('#add_image').trigger('click');
        $('#suppr').trigger('click');
    }


    /**********************             Gestion de l'affichage des galeries               ************************/
    var $images = $('.docs-pictures');
    var options = {
        interval: 2500,
        inline: true,
        movable: false,
        zoomable: false,
        rotatable: false,
        scalable: false,
        title: false,
        url: 'data-original',
        build: function (e) {
            console.log(e.type);
        },
        built: function (e) {
            console.log(e.type);
            if($('#courant')[0] != undefined) { // on vérifie si on est dans une galerie privée
                preview();

                $('#btnselect').click(function (e) {
                    preview();
                });

                $('.viewer-fullscreen').click(function (e){
                    // toggle class du bouton
                    $('#btnselect').toggleClass('selectfs');
                });
            }
        },
        show: function (e) {
            console.log(e.type);
        },
        shown: function (e) {
            console.log(e.type);
        },
        hide: function (e) {
            console.log(e.type);
        },
        hidden: function (e) {
            console.log(e.type);
        },
        view: function (e) {
            console.log(e.type);
        },
        viewed: function (e) {
            console.log(e.type);
            // après affichage d'une image dans le slide
            if($('#courant')[0] != undefined){ // on vérifie si on est dans une galerie privée
                // On cherche dans la liste des miniature celle qui est sélectionnée
                $list = $('.viewer-list').find('li');
                i = parseInt($('.viewer-list li.viewer-active').find('img').attr('data-index'));

                // affichage du numéro de l'image
                $('#courant')[0].innerHTML = 'image ' + (i+1) + ' sur ' + $list.length;

                $listimg = $('.docs-pictures').find('img');
                // on récupère l'id de l'image et si elle est sélectionnée ou pas
                $id = $listimg[i].attributes['data-id'].value;
                $('#imgId').attr('value', $id);

                $('#select')[0].checked = $listimg[i].hasAttribute('selected');

                if($('#select')[0].checked){
                    $('#btnselect').removeClass('btn-primary').removeClass('btn-info').addClass('btn-info');
                    $('#btnselect')[0].innerHTML = 'Retirer cette photo';
                } else {
                    $('#btnselect').removeClass('btn-primary').removeClass('btn-info').addClass('btn-primary');
                    $('#btnselect')[0].innerHTML = 'Garder cette photo';
                }
            } else {
                //modification de la meta og:image
                i = parseInt($('.viewer-list li.viewer-active').find('img').attr('data-index'));
                $listimg = $('.docs-pictures').find('img');
                // on récupère l'id de l'image et si elle est sélectionnée ou pas
                $src = $listimg[i].attributes['src'].value;
                $('meta[property=\'og:image\']')[0].content = $src;
            }

        }
    };

    $images.viewer(options);

    // on désactive le clic-droit sur le document histoire que les images ne soit pas téléchargées
    $(document).contextmenu(function (e){ // on attrape l'évenement "menu contextuel"
        return false;
    });

    if((autoplay !== undefined) && (autoplay == true)){
        $images.viewer('play');
    }

    /**********************             Gestion de la preview des photos sélectionnées dans la galerie privée               ************************/

    function preview(){
        $output = $('#preview')[0];
        $thumbs = $('.docs-pictures').find('img[selected]');
        nb = $thumbs.length;
        $output.innerHTML = '';

        activeIndex = parseInt($('.viewer-list li.viewer-active').find('img').attr('data-index'));
        sel = $('.docs-pictures').find('img')[activeIndex].hasAttribute('selected');

        if(sel){
            $('#btnselect')[0].innerHTML = 'Retirer cette photo';
            $('#btnselect').removeClass('btn-info').removeClass('btn-primary').addClass('btn-info');
        } else {
            $('#btnselect')[0].innerHTML = 'Garder cette photo';
            $('#btnselect').removeClass('btn-info').removeClass('btn-primary').addClass('btn-primary');

        }

        $p = $('<p>');
        $p.id = 'nbselect';
        $p.html('Vous avez selectionné <strong>' + nb + ' image(s)</strong>');
        $p.appendTo($output);

        if(nb > 0){
            // le bloc de visualisation des miniatures
            $div = $('<div>');
            $div.attr('id' , 'thumbs');
            $div.addClass('row');

            i = 0; // compteur d'index
            $thumbs.each(function(){
                $clone = $(this).clone();
                while (!$('.docs-pictures').find('img')[i].hasAttribute('selected')){
                    i++;
                }
                $clone.attr('data-index', i).addClass('col-sm-3').css('margin', '15px 0').appendTo($div).click(function(e){
                    $('#imgId').attr('value', $(e.target).attr('data-id'));
                    console.log($(e.target).attr('data-index'));
                    $('img[class=col-sm-3]').fadeTo('fast', 1, function(){
                        $(e.target).fadeTo('fast', 0.6);
                    });

                    $('.viewer-list').find('img[data-index=' + $(e.target).attr('data-index') + ']').click();
                });
                i++;
            });

            $div.appendTo($output);
        }

    }



    /**********************             demande de confirmation avant suppression               ************************/
    $delLinks = $('.suppr');
    $delLinks.click(function(e){
        if(!confirm('Voulez-vous vraiment supprimer ceci ?')){
            e.preventDefault();
        }
    });

    /**********************             démarrage du drag'n'drop d'upload de fichiers               ************************/

/* ce code ne sert à rien car la dropzone et déclarée sur la div qui a la class dropzone
Je le garde au cas où je change de métode

//je récupère l'action où sera traité l'upload en PHP
    var _actionToDropZone = $("#form_snippet_image").attr('action');

//je définis ma zone de drop grâce à l'ID de ma div citée plus haut.
    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone("#form_snippet_image", { url: _actionToDropZone });

    myDropzone.on("addedfile", function(file) {
        alert('nouveau fichier reçu');
    });

    */

    if($('section').find('img').attr('src') !== undefined){
        $('meta[property=\'og:image\']')[0].content = $('section').find('img').attr('src');
    }

});

