<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Entity\Page;
use CoreBundle\Form\PageType;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CoreBundle:Default:index.html.twig');
    }

    /**
     * Fonction de création d'une page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $p = new Page();
        $form = $this->createForm('CoreBundle\Form\PageType', $p);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $p = $form->getData();
            $em->persist($p);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'La page '. $p->getTitle().' a été créée avec succés !');
            return $this->redirectToRoute('edit_page', array('id' => $p->getId()));
        }

        return $this->render('CoreBundle:Default:add.html.twig', array('form' => $form->createView()));
    }

    /**
     * Fonction d'édition d'une page
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository('CoreBundle:Page')->find($id);
        $form = $this->createForm('CoreBundle\Form\PageType', $page);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $p = $form->getData();
            $em->persist($p);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Les modifications ont été enregistrées avec succés !');
            return $this->redirectToRoute('edit_page', array('id' => $page->getId()));
        }

        $pages = $em->getRepository('CoreBundle:Page')->findAll();
        foreach ($pages as $p){
            $p->setPublication(true);
            $em->persist($p);
        }
        $em->flush();

        return $this->render('CoreBundle:Default:edit.html.twig', array(
            'pages' => $pages,
            'page'     => $page,
            'form'  => $form->createView()
        ));
    }

    /**
     * Fonction de suppression d'une page
     * @param $id
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CoreBundle:Page')->find($id);

        $em->remove($page);
        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Affichage d'une page selon son slug
     * @param $slug
     * @return Response
     */
    public function showAction($slug)
    {

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CoreBundle:Page')->findOneBy(array('slug' => $slug));

        return $this->render('CoreBundle:Default:show.html.twig', array(
            'page' => $page
        ));
    }

    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();
        $galeries = $em->getRepository('CoreBundle:Galerie')->findAll();
        $pages = $em->getRepository('CoreBundle:Page')->findAll();

        return $this->render('CoreBundle:Default:admin.html.twig', array(
            'galeries' => $galeries,
            'pages'    => $pages
        ));
    }

    public function menuAction()
    {
        // modif à apporter : gérer le menu comme une liste de liens internes ou pas selon le slug

        $em = $this->getDoctrine()->getManager();
        $listeLiens = $em->getRepository('CoreBundle:Page')->findAll();

        return $this->render('CoreBundle:Default:menu.html.twig', array(
            'listeLiens' => $listeLiens
        ));
    }
}
