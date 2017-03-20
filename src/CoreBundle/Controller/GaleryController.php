<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Entity\Galerie;
use CoreBundle\Form\GalerieType;
use CoreBundle\Form\GalerieEditType;

class GaleryController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $galeries = $em->getRepository('CoreBundle:Galerie')->findAll();

        return $this->render('CoreBundle:Galery:index.html.twig', array(
            'galeries' => $galeries
        ));
    }

    public function addAction(Request $request)
    {
        $galery = new Galerie();
        $form = $this->createForm(GalerieType::class, $galery);

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

            $request->getSession()->getFlashBag()->add('success', 'Le téléchargement est terminé !');
            return $this->redirectToRoute('edit_galery', array('id' => $galery->getId()));
        }

        return $this->render('CoreBundle:Galery:add.html.twig', array('form' => $form->createView()));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $galery = $em->getRepository('CoreBundle:Galerie')->find($id);
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

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $galery = $em->getRepository('CoreBundle:Galerie')->find($id);

        $em->remove($galery);
        $em->flush();

        return $this->redirectToRoute('galery_index');
    }

    public function deleteImgAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'image et sa galerie associée
        $img = $em->getRepository('CoreBundle:Image')->find($id);
        $galery = $img->getGalerie();

        // On retire l'image de la galerie
        $galery->removeImage($img);

        // On supprime l'image
        $em->remove($img);

        // MAJ finale
        $em->flush();

        return $this->redirectToRoute('edit_galery', array('id' => $galery->getId()));
    }
}

