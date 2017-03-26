<?php

namespace Rgies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rgies\MetricsBundle\Entity\Widgets;
use Rgies\MetricsBundle\Form\WidgetsType;

/**
 * Widgets controller.
 *
 * @Route("/widgets")
 */
class WidgetsController extends Controller
{

    /**
     * Lists all Widgets entities.
     *
     * @Route("/", name="widgets")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Widgets')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Widgets entity.
     *
     * @Route("/", name="widgets_create")
     * @Method("POST")
     * @Template("MetricsBundle:Widgets:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Widgets();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('widgets_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Widgets entity.
     *
     * @param Widgets $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Widgets $entity)
    {
        $form = $this->createForm(new WidgetsType($this->container), $entity, array(
            'action' => $this->generateUrl('widgets_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Widgets entity.
     *
     * @Route("/new", name="widgets_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Widgets();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Widgets entity.
     *
     * @Route("/{id}", name="widgets_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widgets entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Widgets entity.
     *
     * @Route("/{id}/edit", name="widgets_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widgets entity.');
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
    * Creates a form to edit a Widgets entity.
    *
    * @param Widgets $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Widgets $entity)
    {
        $form = $this->createForm(new WidgetsType($this->container), $entity, array(
            'action' => $this->generateUrl('widgets_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Widgets entity.
     *
     * @Route("/{id}", name="widgets_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Widgets:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widgets entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('widgets_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Widgets entity.
     *
     * @Route("/{id}", name="widgets_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Widgets entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('widgets'));
    }

    /**
     * Creates a form to delete a Widgets entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('widgets_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
