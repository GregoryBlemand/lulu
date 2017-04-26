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

            $compte = $em->getRepository('CoreBundle:Lien')->getNbLiens();
            if($link->getOrdre() === null ){
                $link->setOrdre((int)$compte[0][1] + 1);
            }
            var_dump($link);
            die();

            // persister le lien
            //$em->persist($link);
            //$em->flush();
        }

        return $this->render('CoreBundle:Link:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(){
        return $this->redirectToRoute('core_adminpage');
    }

    public function deleteAction(){
        return $this->redirectToRoute('core_adminpage');
    }

    public function viewMenuAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT lien
            FROM CoreBundle:Lien lien
            WHERE lien.ordre != \'null\' 
            ORDER BY lien.ordre ASC'
        );

        $liens = $query->getResult();

        return $this->render('CoreBundle:Default:menu.html.twig', array(
            'liens' => $liens
        ));
    }
}
