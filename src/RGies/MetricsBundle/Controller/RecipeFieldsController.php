<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\RecipeFields;
use RGies\MetricsBundle\Entity\Recipe;
use RGies\MetricsBundle\Form\RecipeFieldsType;

/**
 * RecipeFields controller.
 *
 * @Route("/recipefields")
 */
class RecipeFieldsController extends Controller
{
    /**
     * Creates a new RecipeFields entity.
     *
     * @Route("/create/{recipeId}", name="recipefields_create")
     * @Method("POST")
     * @Template("MetricsBundle:RecipeFields:new.html.twig")
     */
    public function createAction(Request $request, $recipeId)
    {
        $em = $this->getDoctrine()->getManager();
        $recipe = $em->getRepository('MetricsBundle:Recipe')->find($recipeId);

        $entity = new RecipeFields();
        $entity->setRecipe($recipe);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('recipe_edit', array('id' => $recipeId) ));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a RecipeFields entity.
     *
     * @param RecipeFields $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(RecipeFields $entity)
    {
        $form = $this->createForm(new RecipeFieldsType(), $entity, array(
            'action' => $this->generateUrl('recipefields_create', array('recipeId' => $entity->getRecipe()->getId())),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new RecipeFields entity.
     *
     * @Route("/new/{recipeId}", name="recipefields_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($recipeId)
    {
        $em = $this->getDoctrine()->getManager();
        $recipe = $em->getRepository('MetricsBundle:Recipe')->find($recipeId);

        $entity = new RecipeFields();
        $entity->setRecipe($recipe);

        $form   = $this->createCreateForm($entity);

        return array(
            'recipe_id' => $recipeId,
            'entity'    => $entity,
            'form'      => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing RecipeFields entity.
     *
     * @Route("/{id}/edit", name="recipefields_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:RecipeFields')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RecipeFields entity.');
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
    * Creates a form to edit a RecipeFields entity.
    *
    * @param RecipeFields $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(RecipeFields $entity)
    {
        $form = $this->createForm(new RecipeFieldsType(), $entity, array(
            'action' => $this->generateUrl('recipefields_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing RecipeFields entity.
     *
     * @Route("/{id}", name="recipefields_update")
     * @Method("PUT")
     * @Template("MetricsBundle:RecipeFields:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:RecipeFields')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RecipeFields entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('recipe_edit', array('id' => $entity->getRecipe()->getId()) ));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a RecipeFields entity.
     *
     * @Route("/{id}", name="recipefields_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MetricsBundle:RecipeFields')->find($id);

        $recipeId = $entity->getRecipe()->getId();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RecipeFields entity.');
        }

        if ($form->isValid()) {
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('recipe_edit', array('id' => $recipeId )));
    }

    /**
     * Creates a form to delete a RecipeFields entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('recipefields_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Reorder fields by given id list.
     *
     * @Route("/reorderFields/", name="recipefields_reorder")
     * @Method("POST")
     * @return Response
     */
    public function reorderFieldsAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $response = array();
        $recipeId = $request->request->get('recipe_id');
        $ids = $request->request->get('id_list', '');

        $list = array_flip(explode(',', trim($ids, ',')));

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('MetricsBundle:RecipeFields')->findBy(
            array('recipe' => $recipeId)
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

}
