<?php

namespace CoreBundle\Controller;


use CoreBundle\Entity\Lien;
use CoreBundle\Form\LienType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MenuController extends Controller
{
    public function addAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $link = new Lien();
        $form = $this->createForm(LienType::class, $link);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

            // persister la galerie (et en cascade, les images)
            $em->persist($link);
            $em->flush();
        }

        $galeries = $em->getRepository('CoreBundle:Galerie')->findAll();
        $pages = $em->getRepository('CoreBundle:Page')->findAll();
        return $this->render('CoreBundle:Link:add.html.twig', array(
            'form' => $form->createView(),
            'galeries' => $galeries,
            'pages' => $pages
        ));
    }

    public function editAction(){

    }

    public function supprAction(){

    }

    public function viewMenuAction(){

    }
}