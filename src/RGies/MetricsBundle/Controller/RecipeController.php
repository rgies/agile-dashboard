<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Recipe;
use RGies\MetricsBundle\Form\RecipeType;

/**
 * Recipe controller.
 *
 * @Route("/recipe")
 */
class RecipeController extends Controller
{

    /**
     * Lists all Recipe entities.
     *
     * @Route("/", name="recipe")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Recipe')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Recipe entity.
     *
     * @Route("/", name="recipe_create")
     * @Method("POST")
     * @Template("MetricsBundle:Recipe:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Recipe();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // $file stores the uploaded PNG thumbnail file
            if ($entity->getImageUrl()) {
                $file = $entity->getImageUrl();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('recipe_directory'), $fileName);
                $entity->setImageUrl($fileName);
            }

            // $file stores the uploaded JSON config file
            if ($entity->getJsonConfig()) {
                $file = $entity->getJsonConfig();
                $fileName = md5(uniqid()) . '.json';
                $file->move($this->getParameter('recipe_directory'), $fileName);
                $entity->setJsonConfig($fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('recipe_edit', array('id' => $entity->getId() )));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Recipe entity.
     *
     * @param Recipe $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Recipe $entity)
    {
        $form = $this->createForm(new RecipeType($this->container), $entity, array(
            'action' => $this->generateUrl('recipe_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Recipe entity.
     *
     * @Route("/new", name="recipe_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Recipe();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Recipe entity.
     *
     * @Route("/{id}/edit", name="recipe_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Recipe')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Recipe entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $fields = $em->getRepository('MetricsBundle:RecipeFields')->findBy(
            array('recipe' => $id),
            array('pos' => 'ASC')
        );

        return array(
            'fields'      => $fields,
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Recipe entity.
    *
    * @param Recipe $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Recipe $entity)
    {
        $entity->setImageUrl(
            new File($this->getParameter('recipe_directory') . '/' . $entity->getImageUrl(), false)
        );

        $entity->setJsonConfig(
            new File($this->getParameter('recipe_directory') . '/' . $entity->getJsonConfig(), false)
        );

        $form = $this->createForm(new RecipeType($this->container), $entity, array(
            'action' => $this->generateUrl('recipe_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Recipe entity.
     *
     * @Route("/{id}", name="recipe_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Recipe:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Recipe')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Recipe entity.');
        }

        $oldImageUrl = $entity->getImageUrl();
        $oldJsonConfig = $entity->getJsonConfig();

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // $file stores the uploaded PNG thumbnail file
            if ($entity->getImageUrl()) {
                $file = $entity->getImageUrl();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('recipe_directory'), $fileName);
                $entity->setImageUrl($fileName);
                @unlink($this->getParameter('recipe_directory') . '/' . $oldImageUrl);
            } else {
                $entity->setImageUrl($oldImageUrl);
            }

            // $file stores the uploaded JSON config file
            if ($entity->getJsonConfig()) {
                $file = $entity->getJsonConfig();
                $fileName = md5(uniqid()) . '.json';
                $file->move($this->getParameter('recipe_directory'), $fileName);
                $entity->setJsonConfig($fileName);
                @unlink($this->getParameter('recipe_directory') . '/' . $oldJsonConfig);
            } else {
                $entity->setJsonConfig($oldJsonConfig);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('recipe'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Recipe entity.
     *
     * @Route("/{id}", name="recipe_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Recipe')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Recipe entity.');
            }

            @unlink($this->getParameter('recipe_directory') . '/' . $entity->getImageUrl());
            @unlink($this->getParameter('recipe_directory') . '/' . $entity->getJsonConfig());

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('recipe'));
    }

    /**
     * Creates a form to delete a Recipe entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('recipe_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
