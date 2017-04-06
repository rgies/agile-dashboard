<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Dashboard;
use RGies\MetricsBundle\Form\DashboardType;

/**
 * Dashboard controller.
 *
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{

    /**
     * Lists all Dashboard entities.
     *
     * @Route("/", name="dashboard")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Dashboard')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Dashboard entity.
     *
     * @Route("/", name="dashboard_create")
     * @Method("POST")
     * @Template("MetricsBundle:Dashboard:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Dashboard();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Dashboard entity.
     *
     * @param Dashboard $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Dashboard $entity)
    {
        $form = $this->createForm(new DashboardType(), $entity, array(
            'action' => $this->generateUrl('dashboard_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Dashboard entity.
     *
     * @Route("/new", name="dashboard_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Dashboard();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Dashboard entity.
     *
     * @Route("/{id}/edit", name="dashboard_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Dashboard entity.
    *
    * @param Dashboard $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Dashboard $entity)
    {
        $form = $this->createForm(new DashboardType(), $entity, array(
            'action' => $this->generateUrl('dashboard_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Dashboard:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Dashboard entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('dashboard'));
    }

    /**
     * Creates a form to delete a Dashboard entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('dashboard_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
