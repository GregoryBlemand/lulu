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
     * Fonction d'affichage d'une galerie
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
}

