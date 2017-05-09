<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\UserAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Useradmin controller.
 *
 */
class UserAdminController extends Controller
{
    /**
     * Lists all userAdmin entities.
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userAdmins = $em->getRepository('CoreBundle:UserAdmin')->findAll();

        return $this->render('useradmin/index.html.twig', array(
            'userAdmins' => $userAdmins,
        ));
    }

    /**
     * Creates a new userAdmin entity.
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $userAdmin = new Useradmin();
        $form = $this->createForm('CoreBundle\Form\UserAdminType', $userAdmin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userAdmin);
            $em->flush();

            return $this->redirectToRoute('user_show', array('id' => $userAdmin->getId()));
        }

        return $this->render('useradmin/new.html.twig', array(
            'userAdmin' => $userAdmin,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a userAdmin entity.
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction(UserAdmin $userAdmin)
    {
        $deleteForm = $this->createDeleteForm($userAdmin);

        return $this->render('useradmin/show.html.twig', array(
            'userAdmin' => $userAdmin,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing userAdmin entity.
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, UserAdmin $userAdmin)
    {
        $deleteForm = $this->createDeleteForm($userAdmin);
        $editForm = $this->createForm('CoreBundle\Form\UserAdminType', $userAdmin);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', array('id' => $userAdmin->getId()));
        }

        return $this->render('useradmin/edit.html.twig', array(
            'userAdmin' => $userAdmin,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a userAdmin entity.
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, UserAdmin $userAdmin)
    {
        $form = $this->createDeleteForm($userAdmin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userAdmin);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a userAdmin entity.
     *
     * @param UserAdmin $userAdmin The userAdmin entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UserAdmin $userAdmin)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $userAdmin->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
