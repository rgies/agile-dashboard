<?php

namespace RGies\CustomTimelineWidgetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\CustomTimelineWidgetBundle\Entity\WidgetConfig;
use RGies\CustomTimelineWidgetBundle\Form\WidgetConfigType;
use RGies\MetricsBundle\Entity\Widgets;

/**
 * WidgetConfig controller.
 *
 * @Route("/custom_timeline_widget_widgetconfig")
 */
class WidgetConfigController extends Controller
{
    /**
     * Lists all timeline entities.
     *
     * @Route("/{id}", name="CustomTimelineWidgetBundle_widgetconfig_list")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CustomTimelineWidgetBundle:WidgetConfig')->findBy(
            array('widget_id'=>$id),
            array('date'=>'DESC')
        );

        return array(
            'entities' => $entities,
            'widget_id' => $id
        );
    }

    /**
     * Creates a new WidgetConfig entity.
     *
     * @Route("/", name="CustomTimelineWidgetBundle_widgetconfig_create")
     * @Method("POST")
     * @Template("CustomTimelineWidgetBundle:WidgetConfig:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new WidgetConfig();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('CacheService')->deleteValue('CustomTimelineWidgetBundle', $entity->getWidgetId());


            return $this->redirect($this->generateUrl(
                'CustomTimelineWidgetBundle_widgetconfig_list', array('id'=>$entity->getWidgetId())
            ));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a WidgetConfig entity.
     *
     * @param WidgetConfig $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(WidgetConfig $entity)
    {
        $form = $this->createForm(new WidgetConfigType($this->container), $entity, array(
            'action' => $this->generateUrl('CustomTimelineWidgetBundle_widgetconfig_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new WidgetConfig entity.
     *
     * @Route("/new/{id}", name="CustomTimelineWidgetBundle_widgetconfig_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $entity = new WidgetConfig();
        $entity->setWidgetId($id);

        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing WidgetConfig entity.
     *
     * @Route("/{id}/edit", name="CustomTimelineWidgetBundle_widgetconfig_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('CustomTimelineWidgetBundle:WidgetConfig')->createQueryBuilder('i')
                   ->where('i.id = :id')
                   ->setParameter('id', $id);
        $items = $query->getQuery()->getResult();

        $entity = $items[0];
        $editForm = $this->createEditForm($entity);
        $widget = $em->getRepository('MetricsBundle:Widgets')->find($entity->getWidgetId());

        return array(
            'widget'      => $widget,
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Creates a form to edit a WidgetConfig entity.
     *
     * @param WidgetConfig $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(WidgetConfig $entity)
    {
        $form = $this->createForm(new WidgetConfigType($this->container), $entity, array(
            'action' => $this->generateUrl('CustomTimelineWidgetBundle_widgetconfig_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        return $form;
    }

    /**
     * Edits an existing WidgetConfig entity.
     *
     * @Route("/{id}", name="CustomTimelineWidgetBundle_widgetconfig_update")
     * @Method("PUT")
     * @Template("CustomTimelineWidgetBundle:WidgetConfig:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CustomTimelineWidgetBundle:WidgetConfig')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find WidgetConfig entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->get('CacheService')->deleteValue('CustomTimelineWidgetBundle', $entity->getWidgetId());

            return $this->redirect($this->generateUrl(
                'CustomTimelineWidgetBundle_widgetconfig_list', array('id' => $entity->getWidgetId())
            ));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes an existing WidgetConfig entity.
     *
     * @Route("/{widgetId}/delete{id}", name="CustomTimelineWidgetBundle_widgetconfig_delete")
     * @Method("GET")
     * @Template("CustomTimelineWidgetBundle:WidgetConfig:index.html.twig")
     */
    public function deleteAction($widgetId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        // clear widget data cache
        $em->createQuery('delete from CustomTimelineWidgetBundle:WidgetConfig st where st.id = :id')
            ->setParameter('id', $id)
            ->execute();


        return $this->redirect($this->generateUrl(
            'CustomTimelineWidgetBundle_widgetconfig_list', array('id'=>$widgetId)
        ));
    }

}
