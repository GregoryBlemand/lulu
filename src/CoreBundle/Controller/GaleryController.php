<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Image;
use CoreBundle\Entity\Lien;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Entity\Galerie;
use CoreBundle\Form\GalerieType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class GaleryController extends Controller
{
    /**
     * Fonction d'ajout d'une galerie
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $galery = new Galerie();
        $form = $this->createForm(GalerieType::class, $galery);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $galery = $form->getData();

            // génération du lien/slug
            $link = new Lien();
            $galery->setLien($link);

            // On récupère les images envoyées
            $files = $galery->getImages();
            foreach ($files as $file) { // on leur assigne ma galerie courante
                $file->setGalerie($galery);
            }

            // persister la galerie (et en cascade, les images)
            $em = $this->getDoctrine()->getManager();
            $em->persist($galery);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le téléchargement est terminé !');
            return $this->redirectToRoute('edit_galery', array('id' => $galery->getId()));
        }

        return $this->render('CoreBundle:Galery:add.html.twig', array('form' => $form->createView()));
    }

    /**
     * Fonction d'edition d'une galerie
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $galery = $em->getRepository('CoreBundle:Galerie')->find($id);

        if($galery === null){
            throw new NotFoundHttpException('La galerie à éditer est introuvable.');
        }

        $form = $this->createForm(GalerieType::class, $galery);

        $files = $galery->getImages();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $galery = $form->getData();
            // On récupère les images envoyées

            $files = $galery->getImages();
            foreach ($files as $file) { // on leur assigne ma galerie courante
                $file->setGalerie($galery);
            }

            // persister la galerie (et en cascade, les images)
            $em = $this->getDoctrine()->getManager();
            $em->persist($galery);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Mise à jour terminée !');
            return $this->redirectToRoute('edit_galery', array('id' => $galery->getId()));
        }

        return $this->render('CoreBundle:Galery:edit.html.twig', array(
            'form' => $form->createView(),
            'images' => $files,
            'galerie' => $galery
        ));
    }

    /**
     * Fonction de suppression d'une galerie
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $galery = $em->getRepository('CoreBundle:Galerie')->find($id);

        if($galery === null){
            throw new NotFoundHttpException('La galerie à supprimer est introuvable.');
        }

        // si la galerie est assignée à un utilisateur, on lui retire cette galerie
        if($galery->getUser() !== null){
            $user = $galery->getUser();
            $user->removeGalery($galery);
        }

        $em->remove($galery);
        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Fonction de suppression d'une image d'une galerie
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteImgAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'image et sa galerie associée
        $img = $em->getRepository('CoreBundle:Image')->find($id);

        if($img === null){
            throw new NotFoundHttpException('L\'image à supprimer est introuvable.');
        }

        $galery = $img->getGalerie();

        // On retire l'image de la galerie
        $galery->removeImage($img);

        // On supprime l'image
        $em->remove($img);

        // MAJ finale
        $em->flush();

        return $this->redirectToRoute('edit_galery', array('id' => $galery->getId()));
    }

    /**
     * Fonction d'affichage d'une galerie standard
     *
     * @return Response
     */
    public function viewAction($id){
        $em = $this->getDoctrine()->getManager();
        $galerie = $em->getRepository('CoreBundle:Galerie')->find($id);

        if($galerie === null){
            throw new NotFoundHttpException('La galerie demandée n\'a pas été trouvée.');
        }

        return $this->render('CoreBundle:Galery:view.html.twig', array(
            'galerie' => $galerie
        ));
    }

    /**
     * Fonction d'upload ajax d'image dans une galerie
     * @param $id de la galerie courante
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxSnippetImageSendAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $g = $em->getRepository('CoreBundle:Galerie')->find($id);

        if(null === $g){
            throw new NotFoundHttpException("Galerie demandée inconnue...");
        }

        $image = new Image();
        $media = $request->files->get('file');

        $image->setFile($media);
        $image->setAlt('Lucile Ortega - Photographie');

        // il faut ajouter l'image à la galerie
        $g->addImage($image);

        $em->flush();

        //infos sur le document envoyé
        //var_dump($request->files->get('file'));die;
        return new JsonResponse(array('success' => true));
    }

    /**
     * Fonction d'affichage d'une galerie privée
     * @param $id
     * @return Response
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function privateGaleryAction($id){
        /*
         * Gestion de l'affichage des galerie privée...
         *
         * On vérifie si l'utilisateur est connecté et s'il a le droit de voir cette galerie.
         * Il a le droit si la galerie lui est assignée ou s'il a les droits admin
         *
         * si c'est le cas, on affiche la galerie avec un champs de sélection par image
         * (mise à jour en ajax d'un champs selected de la galerie en question)
         *
         */
        $user = $this->getUser();

        if (null === $user) {
            // Ici, l'utilisateur est anonyme ou l'URL n'est pas derrière un pare-feu
            return $this->redirectToRoute('login');
        } else {
            // Ici, $user est une instance de notre classe User
            if($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN') || ($user->hasRole('ROLE_CLIENT') && $user->hasGalery($id))){
                $em = $this->getDoctrine()->getManager();
                $galerie = $em->getRepository('CoreBundle:Galerie')->find($id);

                if($galerie === null){
                    throw new NotFoundHttpException('La galerie demandée n\'a pas été trouvée.');
                }

                return $this->render('CoreBundle:Galery:private.html.twig', array(
                    'galerie' => $galerie
                ));
            } else {
                $this->addFlash('success', 'Vous n\'êtes pas autorisé à consulter le contenu demandé.');
                return $this->redirectToRoute('core_homepage');
            }
        }
    }

    /**
     * Fonction de selection ajax des images dans les galeries privées
     * @param $id
     * @return JsonResponse
     */
    public function ajaxSelectImgAction($id){
        $em = $this->getDoctrine()->getManager();
        $img = $em->getRepository('CoreBundle:Image')->find($id);

        if(null === $img){
            throw new NotFoundHttpException("Galerie demandée inconnue...");
        }

        if($img->getSelected()){
            $img->setSelected(false);
        } else {
            $img->setSelected(true);
        }

        $em->flush();

        return new JsonResponse(array('select' => $img->getSelected(), 'id' => $id));
    }

    /**
     * Fonction de listage des shootings d'un client
     * @return Response
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function shootingsAction(){
        // On récupère l'utilisateur courant
        $user = $this->getUser();

        if (null === $user) {
            // Ici, l'utilisateur est anonyme ou l'URL n'est pas derrière un pare-feu
            return $this->redirectToRoute('login');
        } else {
            // Ici, $user est une instance de notre classe User
            $em = $this->getDoctrine()->getManager();

            // Si l'user est un admin ou un super admin, on récupère tous les galeries
            if($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')){
                $galeries = $em->getRepository('CoreBundle:Galerie')->findAll();
                $users = $em->getRepository('CoreBundle:UserAdmin')->findAll();
            } elseif ($user->hasRole('ROLE_CLIENT')){
                $galeries = $em->getRepository('CoreBundle:Galerie')->findBy(array('user' => $user));
                $users = null;
            }

            return $this->render('CoreBundle:Galery:shootings.html.twig', array(
                'galeries' => $galeries,
                'users' => $users
            ));
        }
    }

    /**
     * Fonction qui assigne une galerie à un utilisateur.
     * @param $galery - id de la galerie à assigner
     * @param $user - id de l'utilisateur à assigner
     * @return JsonResponse
     */
    public function ajaxAddGaleryUserAction($galery, $user){
        // mise à jour des galeries et utilisateurs pour assigner un user aux galeries privées
        $em = $this->getDoctrine()->getManager();
        $g = $em->getRepository('CoreBundle:Galerie')->find($galery);
        // je récupère l'utilisateur de la galerie et je lui enlève
        $tempU = $g->getUser();
        if($tempU !== null){
            $tempU->removeGalery($g);
        }
        if ($user !== 'null'){
            // on récupère l'utilisateur
            $client = $em->getRepository('CoreBundle:UserAdmin')->find($user);
            if($client == null){
                return new JsonResponse(array('success' => false, 'msg' => 'Client introuvable...'));
            }
            $client->addGalery($g);
            $em->flush();
            return new JsonResponse(array('success' => true, 'msg' => 'La galerie a été assignée à ' . $client->getUsername()));

        } else { // si le $user est null, c'est qu'on désassigne la galerie (ce qui a été fait plus haut)
            $em->flush();
            return new JsonResponse(array('success' => true, 'msg' => 'Pas d\'utilisateur sélectionné'));
        }
    }

}
