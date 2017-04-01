<?php

namespace Rgies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rgies\MetricsBundle\Entity\JiraCountConfig;
use Rgies\MetricsBundle\Form\JiraCountConfigType;

/**
 * JiraCountConfig controller.
 *
 * @Route("/jiracountconfig")
 */
class JiraCountConfigController extends Controller
{

    /**
     * Lists all JiraCountConfig entities.
     *
     * @Route("/", name="jiracountconfig")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:JiraCountConfig')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new JiraCountConfig entity.
     *
     * @Route("/", name="jiracountconfig_create")
     * @Method("POST")
     * @Template("MetricsBundle:JiraCountConfig:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new JiraCountConfig();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //return $this->redirect($this->generateUrl('jiracountconfig_show', array('id' => $entity->getId())));
            return $this->redirect($this->generateUrl('home'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a JiraCountConfig entity.
     *
     * @param JiraCountConfig $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(JiraCountConfig $entity)
    {
        $form = $this->createForm(new JiraCountConfigType(), $entity, array(
            'action' => $this->generateUrl('jiracountconfig_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('id', 'hidden', array('attr' => array('name' => 'id', 'value' => $id)));
        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new JiraCountConfig entity.
     *
     * @Route("/new/{id}", name="jiracountconfig_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $entity = new JiraCountConfig();
        $entity->setWidgetId($id);
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a JiraCountConfig entity.
     *
     * @Route("/{id}", name="jiracountconfig_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:JiraCountConfig')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find JiraCountConfig entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing JiraCountConfig entity.
     *
     * @Route("/{id}/edit", name="jiracountconfig_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->_getConfigEntity($id);

        //$entity = $em->getRepository('MetricsBundle:JiraCountConfig')->find($id);

        if (!$entity) {
            return $this->forward('MetricsBundle:JiraCountConfig:new', array('id' => $id));
            //throw $this->createNotFoundException('Unable to find JiraCountConfig entity.');
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
    * Creates a form to edit a JiraCountConfig entity.
    *
    * @param JiraCountConfig $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(JiraCountConfig $entity)
    {
        $form = $this->createForm(new JiraCountConfigType(), $entity, array(
            'action' => $this->generateUrl('jiracountconfig_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing JiraCountConfig entity.
     *
     * @Route("/{id}", name="jiracountconfig_update")
     * @Method("PUT")
     * @Template("MetricsBundle:JiraCountConfig:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:JiraCountConfig')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find JiraCountConfig entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('jiracountconfig_edit', array('id' => $id)));
            return $this->redirect($this->generateUrl('home'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a JiraCountConfig entity.
     *
     * @Route("/{id}", name="jiracountconfig_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:JiraCountConfig')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find JiraCountConfig entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('jiracountconfig'));
    }

    /**
     * Creates a form to delete a JiraCountConfig entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('jiracountconfig_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    protected function _getConfigEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        // todo: add flexible widget config objects
        $query = $em->getRepository('MetricsBundle:JiraCountConfig')->createQueryBuilder('i')
            ->where('i.widgetId = :id')
            ->setParameter('id', $id);
        $items = $query->getQuery()->getResult();


        if ($items) {
            return $items[0];
        }

        return null;
    }

}
