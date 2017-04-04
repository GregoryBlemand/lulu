<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use CoreBundle\Entity\Page;
use CoreBundle\Form\PageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     *
     * @Security("has_role('ROLE_ADMIN')")
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
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository('CoreBundle:Page')->find($id);

        if(!$page){
            throw new Exception('La page demandée n\'a pas été trouvée.');
        }

        $form = $this->createForm('CoreBundle\Form\PageType', $page);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $p = $form->getData();
            $em->persist($p);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Les modifications ont été enregistrées avec succés !');
            return $this->redirectToRoute('edit_page', array('id' => $page->getId()));
        }

        return $this->render('CoreBundle:Default:edit.html.twig', array(
            'page'     => $page,
            'form'  => $form->createView()
        ));
    }

    /**
     * Fonction de suppression d'une page
     * @param $id
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CoreBundle:Page')->find($id);

        if(!$page){
            throw new Exception('La page demandée n\'a pas été trouvée.');
        }

        $em->remove($page);
        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Affichage d'une page selon son slug
     * A gérer avec modification du schema entités
     * @param $slug
     * @return Response
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CoreBundle:Page')->findOneBy(array('slug' => $slug));

        if($slug == 'mariages'){
            $galerie = $em->getRepository('CoreBundle:Galerie')->find(20);

            if(!$galerie){
                throw new Exception('La galerie demandée n\'a pas été trouvée.');
            }

            return $this->render('CoreBundle:Galery:view.html.twig', array(
                'galerie' => $galerie
            ));
        }

        if($slug == 'portraits'){
            $galerie = $em->getRepository('CoreBundle:Galerie')->find(25);

            if(!$galerie){
                throw new Exception('La galerie demandée n\'a pas été trouvée.');
            }

            return $this->render('CoreBundle:Galery:view.html.twig', array(
                'galerie' => $galerie
            ));
        }

        if($slug == 'reportages'){
            $galerie = $em->getRepository('CoreBundle:Galerie')->find(26);

            if(!$galerie){
                throw new Exception('La galerie demandée n\'a pas été trouvée.');
            }

            return $this->render('CoreBundle:Galery:view.html.twig', array(
                'galerie' => $galerie
            ));
        }

        return $this->render('CoreBundle:Default:show.html.twig', array(
            'page'    => $page,
        ));
    }

    /**
     * @return Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();
        $galeries = $em->getRepository('CoreBundle:Galerie')->findAll();
        $pages = $em->getRepository('CoreBundle:Page')->findAll();
        $liens = $em->getRepository('CoreBundle:Lien')->findAll();

        return $this->render('CoreBundle:Default:admin.html.twig', array(
            'galeries' => $galeries,
            'pages'    => $pages,
            'liens'    => $liens
        ));
    }
}
