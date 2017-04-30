<?php

namespace CoreBundle\Controller;


use CoreBundle\Entity\Lien;
use CoreBundle\Form\LienType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class MenuController extends Controller
{
    /**
     * Ajout d'un lien au menu de navigation
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $link = new Lien();
        $form = $this->createForm(LienType::class, $link);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

            $compte = $em->getRepository('CoreBundle:Lien')->getNbLiens();
            if($link->getOrdre() === null ){
                $link->setOrdre((int)$compte[0][1] + 1);
            }

            // persister le lien
            $em->persist($link);
            $em->flush();
        }

        $request->getSession()->getFlashBag()->add('success', 'Le lien '. $link->getTitle().' a été créé avec succés !');
        return $this->render('CoreBundle:Link:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edition d'un lien du menu
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();

        $link = $em->getRepository('CoreBundle:Lien')->find($id);

        if($link === null){
            throw new NotFoundHttpException('Lien introuvable.');
        }

        $form = $this->createForm(LienType::class, $link);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

            // persister le lien
            $em->persist($link);
            $em->flush();
        }

        $request->getSession()->getFlashBag()->add('success', 'Le lien '. $link->getTitle().' a été édité avec succés !');
        return $this->render('CoreBundle:Link:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Suppression d'un lien du menu
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $lien = $em->getRepository('CoreBundle:Lien')->find($id);
        if ($lien === null){
            throw new \Exception("Le lien à supprimer n'existe pas !");
        }

        // maintenir la continuité de l'ordre dans le menu
        // quand on supprime un lien menu, chaque élément suivant doit être décalé de 1
        $aDeplacer = $em->getRepository('CoreBundle:Lien')->findBy(array('ordre' => '>'.$lien->getOrdre(), 'ordre' => '!= \'null\''));
        foreach ($aDeplacer as $move){
            $move->setOrdre($move->getOrdre() - 1);
        }

        if($lien->getType() === 'EXTERNE'){ // si c'est un lien externe, on le supprime directement
            $em->remove($lien);
        } else { // si c'est un lien interne, on met l'ordre à null pour qu'il n'apparaisse plus dans le menu
            $lien->setOrdre('null');
        }
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Le lien a été supprimé avec succés !');
        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Déplacement vers le haut d'un lien
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function upLinkAction($id){
        $em = $this->getDoctrine()->getManager();
        $lien = $em->getRepository('CoreBundle:Lien')->find($id);
        $prochainOrdre = $lien->getOrdre() - 1;

        // récupérer le lien au dessus dans l'ordre et interchanger l'ordre
        $aModifier = $em->getRepository('CoreBundle:Lien')->findOneBy(array('ordre' => $prochainOrdre));
        $lien->setOrdre($prochainOrdre);
        $aModifier->setOrdre($aModifier->getOrdre() + 1);

        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Descente d'un lien du menu
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function downLinkAction($id){
        $em = $this->getDoctrine()->getManager();
        $lien = $em->getRepository('CoreBundle:Lien')->find($id);
        $prochainOrdre = $lien->getOrdre() + 1;

        // récupérer le lien au dessus dans l'ordre et interchanger l'ordre
        $aModifier = $em->getRepository('CoreBundle:Lien')->findOneBy(array('ordre' => $prochainOrdre));
        $lien->setOrdre($prochainOrdre);
        $aModifier->setOrdre($aModifier->getOrdre() - 1);

        $em->flush();

        return $this->redirectToRoute('core_adminpage');
    }

    /**
     * Affichage du menu
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewMenuAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT lien
            FROM CoreBundle:Lien lien
            WHERE lien.ordre != \'null\' 
            ORDER BY lien.ordre ASC'
        );

        $liens = $query->getResult();

        return $this->render('CoreBundle:Link:menu.html.twig', array(
            'liens' => $liens
        ));
    }
}
