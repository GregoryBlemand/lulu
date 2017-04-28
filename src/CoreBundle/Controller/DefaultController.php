<?php

namespace CoreBundle\Controller;

use CoreBundle\CoreBundle;
use CoreBundle\Entity\Lien;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

            // génération du lien/slug
            $link = new Lien();
            $p->setLien($link);

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

        if($page === null){
            throw new NotFoundHttpException('La page demandée n\'a pas été trouvée.');
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * 
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CoreBundle:Page')->find($id);

        if($page === null){
            // envoie une erreur 400 mauvaise requète http
            throw new NotFoundHttpException('La page demandée n\'a pas été trouvée.');
        }

        $em->remove($page);
        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Affichage d'un contenu selon son slug
     * @param $slug
     * @return Response
     */
    public function showAction($slug)
    {
        // On va chercher dans la table des liens, le slug demandé
        $em = $this->getDoctrine()->getManager();
        $lien = $em->getRepository('CoreBundle:Lien')->findOneBy(array('slug' => $slug));

        if($lien === null){ // on a pas trouvé de lien correspondant
            // envoie une erreur 404 page non-trouvée
            throw new NotFoundHttpException();
        }

        if($lien->getType() === 'PAGE'){ // si le lien renvoie à une page, on la rend avec le template de page
            return $this->render('CoreBundle:Default:show.html.twig', array(
                'page'    => $lien->getPage()
            ));
        } elseif ($lien->getType() === 'GALERIE'){

            // a faire : gérer si la galerie est privée ou non

            return $this->render('CoreBundle:Galery:view.html.twig', array(
                'galerie' => $lien->getGalerie()
            ));
        }

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

        $query = $em->createQuery(
            'SELECT lien
            FROM CoreBundle:Lien lien
            WHERE lien.ordre != \'null\' 
            ORDER BY lien.ordre ASC'
        );

        $liens = $query->getResult();

        return $this->render('CoreBundle:Default:admin.html.twig', array(
            'galeries' => $galeries,
            'pages'    => $pages,
            'liens'    => $liens
        ));
    }
}
