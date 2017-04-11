<?php

namespace RGies\JiraListWidgetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\JiraListWidgetBundle\Entity\WidgetConfig;
use RGies\JiraListWidgetBundle\Form\WidgetConfigType;

/**
 * WidgetConfig controller.
 *
 * @Route("/jira_list_widget_widgetconfig")
 */
class WidgetConfigController extends Controller
{

    /**
     * Creates a new WidgetConfig entity.
     *
     * @Route("/", name="JiraListWidgetBundle_widgetconfig_create")
     * @Method("POST")
     * @Template("JiraListWidgetBundle:WidgetConfig:new.html.twig")
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

            return $this->redirect($this->generateUrl('home'));
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
            'action' => $this->generateUrl('JiraListWidgetBundle_widgetconfig_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new WidgetConfig entity.
     *
     * @Route("/new/{id}", name="JiraListWidgetBundle_widgetconfig_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $entity = new WidgetConfig();
        $entity->setWidgetId($id);

        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing WidgetConfig entity.
     *
     * @Route("/{id}/edit", name="JiraListWidgetBundle_widgetconfig_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('JiraListWidgetBundle:WidgetConfig')->createQueryBuilder('i')
                   ->where('i.widget_id = :id')
                   ->setParameter('id', $id);
        $items = $query->getQuery()->getResult();


        if (!$items) {
            return $this->forward('JiraListWidgetBundle:WidgetConfig:new', array('id' => $id));
        }

        $entity = $items[0];

        $editForm = $this->createEditForm($entity);

        return array(
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
            'action' => $this->generateUrl('JiraListWidgetBundle_widgetconfig_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        return $form;
    }

    /**
     * Edits an existing WidgetConfig entity.
     *
     * @Route("/{id}", name="JiraListWidgetBundle_widgetconfig_update")
     * @Method("PUT")
     * @Template("JiraListWidgetBundle:WidgetConfig:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('JiraListWidgetBundle:WidgetConfig')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find WidgetConfig entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->get('CacheService')->deleteValue('JiraListWidgetBundle', $entity->getWidgetId());

            return $this->redirect($this->generateUrl('home'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

}
