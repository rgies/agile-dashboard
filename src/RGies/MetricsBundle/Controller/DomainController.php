<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Domain;
use RGies\MetricsBundle\Form\DomainType;

/**
 * Domain controller.
 *
 * @Route("/domain")
 */
class DomainController extends Controller
{

    /**
     * Lists all Domain entities.
     *
     * @Route("/", name="domain")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Domain')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Domain entity.
     *
     * @Route("/", name="domain_create")
     * @Method("POST")
     * @Template("MetricsBundle:Domain:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Domain();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('domain'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Domain entity.
     *
     * @param Domain $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Domain $entity)
    {
        $form = $this->createForm(new DomainType(), $entity, array(
            'action' => $this->generateUrl('domain_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Domain entity.
     *
     * @Route("/new", name="domain_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Domain();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Domain entity.
     *
     * @Route("/{id}", name="domain_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Domain entity.
     *
     * @Route("/{id}/edit", name="domain_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
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
    * Creates a form to edit a Domain entity.
    *
    * @param Domain $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Domain $entity)
    {
        $form = $this->createForm(new DomainType(), $entity, array(
            'action' => $this->generateUrl('domain_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Domain entity.
     *
     * @Route("/{id}", name="domain_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Domain:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('domain'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Domain entity.
     *
     * @Route("/{id}", name="domain_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Domain')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Domain entity.');
            }

            // delete all user
            $em->createQuery('delete from MetricsBundle:User u where u.domain = :id')
                ->setParameter('id', $id)
                ->execute();

            // delete all user groups
            $em->createQuery('delete from MetricsBundle:Usergroup u where u.domain = :id')
                ->setParameter('id', $id)
                ->execute();

            // delete all dashboards
            $em->createQuery('delete from MetricsBundle:Dashboard d where d.domain = :id')
                ->setParameter('id', $id)
                ->execute();

            // delete all credentials
            $em->createQuery('delete from MetricsBundle:Credential c where c.domain = :id')
                ->setParameter('id', $id)
                ->execute();


            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('domain'));
    }

    /**
     * Creates a form to delete a Domain entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('domain_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Get html for domain switcher.
     *
     * @Route("/switch-list", name="domain-switch-list")
     * @Method("POST")
     * @Template("MetricsBundle:Domain:switch-list.html.twig")
     */
    public function switchListAjax(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Domain')->findBy(
            array('is_active' => 1)
        );

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Switch the current domain.
     *
     * @Route("/switch/{domainId}", name="domain-switch")
     * @Method("GET")
     */
    public function setDomain(Request $request, $domainId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MetricsBundle:Domain')->find($domainId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        // set the new domain in the session
        $this->get('session')->set('domain', $entity->getId());
        $this->get('session')->set('domain-name', $entity->getTitle());

        // delete last visited dashboard cookie
        $response = new Response();
        $response->headers->clearCookie(DefaultController::LAST_VISITED_DASHBOARD);
        $response->sendHeaders();

        return $this->redirect($request->headers->get('referer'));
    }
}
