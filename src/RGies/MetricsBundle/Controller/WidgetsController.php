<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Widgets;
use RGies\MetricsBundle\Form\WidgetsType;

/**
 * Widgets controller.
 *
 * @Route("/widgets")
 */
class WidgetsController extends Controller
{
    const LAST_VISITED_DASHBOARD = 'last_dashboard_id';

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

            $widgetAction = $this->get('WidgetService')->getWidgetEditActionName($entity->getType());

            //return $this->redirect($this->generateUrl('widgets_show', array('id' => $entity->getId())));
            return $this->redirect($this->generateUrl($widgetAction, array('id' => $entity->getId())));
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
    private function createCreateForm(Widgets $entity, $dashboardEntity=null)
    {
        $form = $this->createForm(new WidgetsType($this->container, $dashboardEntity), $entity, array(
            'action' => $this->generateUrl('widgets_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Widgets entity.
     *
     * @Route("/new", name="widgets_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $lastDashboardId = $request->cookies->get(self::LAST_VISITED_DASHBOARD);
        if (!$lastDashboardId) {
            $lastDashboardId = 1;
        }

        $em = $this->getDoctrine()->getManager();
        $dashboardEntity = $em->getRepository('MetricsBundle:Dashboard')->find($lastDashboardId);

        $entity = new Widgets();
        $form   = $this->createCreateForm($entity, $dashboardEntity);

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
            'attr'   => array('id' => 'edit-form'),
        ));

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

            $this->get('CacheService')->deleteValue($entity->getType(), $entity->getId());

            return $this->redirect($this->generateUrl('start'));
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

            // delete config
            $this->get('WidgetService')->deleteWidgetConfig($entity->getType(), $id);

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
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('widgets_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
            ;
    }

    /**
     * @Route("/disableWidgets/", name="widgets_disable")
     * @Method("POST")
     * @return Response
     */
    public function disableWidgetAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $id = $request->get('id');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widgets entity.');
        }

        $entity->setEnabled(false);
        $em->flush();

        return new Response(json_encode(array()), Response::HTTP_OK);
    }

    /**
     * Reorder widget by given id list.
     *
     * @Route("/reorderWidgets/", name="widgets_reorder")
     * @Method("POST")
     * @return Response
     */
    public function reorderWidgetsAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $response = array();
        $ids = $request->request->get('widgets', '');
        $dashboardId = $request->request->get('dashboard_id');

        $list = array_flip(explode(',', trim($ids, ',')));

        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('MetricsBundle:Widgets');

        $query = $item->createQueryBuilder('w')
            ->where('w.dashboard = :id')
            ->andWhere('w.enabled = 1')
            ->setParameter('id', $dashboardId);

        $entities = $query->getQuery()->getResult();

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
     * Sets new widget dashboard.
     *
     * @Route("/setWidgetDashboard/", name="widgets_set_dashboard")
     * @Method("POST")
     * @return Response
     */
    public function setWidgetDashboardAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $response = array();

        $widgetId = $request->request->get('widgetId');
        $dashboardId = $request->request->get('dashboardId');

        $em = $this->getDoctrine()->getManager();
        $widget = $em->getRepository('MetricsBundle:Widgets')->find($widgetId);
        $dashboard = $em->getRepository('MetricsBundle:Dashboard')->find($dashboardId);

        $widget->setDashboard($dashboard);

        $em->persist($widget);
        $em->flush();

        return new Response(json_encode($response), Response::HTTP_OK);

    }

    /**
     * Copy a Widgets entity.
     *
     * @Route("/copyWidget/", name="widgets_copy")
     * @Template()
     */
    public function copyAction(Request $request)
    {
        $id = $request->get('id');
        $title = $request->get('title');
        $dashboard = $request->get('dashboard');

        $em = $this->getDoctrine()->getManager();
        $widget = $em->getRepository('MetricsBundle:Widgets')->find($id);
        $config = $this->get('widgetService')->getWidgetConfig($widget->getType(), $id);

        $newWidget = clone $widget;
        $newConfig = clone $config;

        $newWidget->setTitle($title);

        if ($dashboard) {
            $dashboard = $em->getRepository('MetricsBundle:Dashboard')->find($dashboard);
            $newWidget->setDashboard($dashboard);
        }

        $em->persist($newWidget);
        $em->flush();

        $newConfig->setWidgetId($newWidget->getId());
        $em->persist($newConfig);
        $em->flush();

        $widgetAction = $this->get('WidgetService')->getWidgetEditActionName($newWidget->getType());

        //return $this->redirect($this->generateUrl('widgets_show', array('id' => $entity->getId())));
        return $this->redirect($this->generateUrl($widgetAction, array('id' => $newWidget->getId())));
    }

}
