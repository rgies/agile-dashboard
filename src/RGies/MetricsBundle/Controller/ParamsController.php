<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Params;
use RGies\MetricsBundle\Form\ParamsType;

/**
 * Params controller.
 *
 * @Route("/params")
 */
class ParamsController extends Controller
{

    /**
     * Lists all Params entities.
     *
     * @Route("/", name="params")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Params')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Params entity.
     *
     * @Route("/create/{dashboardId}", name="params_create")
     * @Method("POST")
     * @Template("MetricsBundle:Params:new.html.twig")
     */
    public function createAction(Request $request, $dashboardId)
    {
        $em = $this->getDoctrine()->getManager();
        $dashboard = $em->getRepository('MetricsBundle:Dashboard')->find($dashboardId);

        $entity = new Params();
        $entity->setDashboard($dashboard);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard_edit', array('id' => $dashboardId) ));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Params entity.
     *
     * @param Params $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Params $entity)
    {
        $form = $this->createForm(new ParamsType(), $entity, array(
            'action' => $this->generateUrl('params_create', array('dashboardId' => $entity->getDashboard()->getId())),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Params entity.
     *
     * @Route("/new/{dashboardId}", name="params_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($dashboardId)
    {
        $em = $this->getDoctrine()->getManager();
        $dashboard = $em->getRepository('MetricsBundle:Dashboard')->find($dashboardId);

        $entity = new Params();
        $entity->setDashboard($dashboard);
        $form   = $this->createCreateForm($entity);

        return array(
            'dashboard_id' => $dashboardId,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Params entity.
     *
     * @Route("/{id}/edit", name="params_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Params')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Params entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id, $entity->getDashboard()->getId());

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Params entity.
    *
    * @param Params $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Params $entity)
    {
        $form = $this->createForm(new ParamsType(), $entity, array(
            'action' => $this->generateUrl('params_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Params entity.
     *
     * @Route("/{id}", name="params_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Params:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Params')->find($id);
        $dashboardId = $entity->getDashboard()->getId();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Params entity.');
        }

        $deleteForm = $this->createDeleteForm($id, $dashboardId);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard_edit', array('id' => $dashboardId)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Params entity.
     *
     * @Route("/{id}/{dashboardId}", name="params_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id, $dashboardId)
    {
        $form = $this->createDeleteForm($id, $dashboardId);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Params')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Params entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('dashboard_edit', array('id' => $dashboardId)));
    }

    /**
     * Creates a form to delete a Params entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id, $dashboardId)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('params_delete', array('id' => $id, 'dashboardId' => $dashboardId)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Lists all Params entities.
     *
     * @Route("/config/{id}", name="params_config")
     * @Method("GET")
     * @Template()
     */
    public function configAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Params')->findBy(
            array('dashboard' => $id),
            array('pos' => 'ASC')
        );

        return array(
            'dashboardId' => $id,
            'entities' => $entities,
        );
    }

    /**
     * Reorder params by given id list.
     *
     * @Route("/reorderParams/", name="params_reorder")
     * @Method("POST")
     * @return Response
     */
    public function reorderParamsAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $response = array();
        $dashboardId = $request->request->get('dashboard_id');
        $ids = $request->request->get('id_list', '');

        $list = array_flip(explode(',', trim($ids, ',')));

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('MetricsBundle:Params')->findBy(
            array('dashboard' => $dashboardId)
        );

        foreach ($entities as $entity)
        {
            if (isset($list[$entity->getId()]))
            {
                $entity->setPos($list[$entity->getId()]);
                $em->persist($entity);
            }
            else
            {
                $entity->setPos(999);
                $em->persist($entity);
            }
        }
        $em->flush();

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Save dashboard parameter.
     *
     * @Route("/save", name="params_save")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {
        $dashboardId = $request->request->get('dashboardId');
        $params = $request->request->get('dashboard_params');

        $em = $this->getDoctrine()->getManager();

        foreach ($params as $key=>$value)
        {
            $entity = $em->getRepository('MetricsBundle:Params')->find($key);

            if ($entity) {
                $entity->setValue($value);
                $em->persist($entity);
            }

        }
        $em->flush();

        // clear widget cache
        $this->get('CacheService')->deleteCacheByDashboardId($dashboardId);

        return $this->redirect($this->generateUrl('home', array('id' => $dashboardId)));
    }

}
