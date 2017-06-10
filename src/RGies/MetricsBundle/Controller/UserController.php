<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\User;
use RGies\MetricsBundle\Form\UserType;
use RGies\MetricsBundle\Form\UserEditType;
use Symfony\Component\HttpFoundation\Response;


/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:User')->findBy(
            array('domain' => $this->get('session')->get('domain')),
            array('lastname' => 'ASC')
        );

        return array(
            'entities'  => $entities,
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Template("MetricsBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setDomain($request->getSession()->get('domain'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('user'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType($this->container), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if (!$this->get('AclService')->userHasEntityAccess($entity)) {
            throw $this->createNotFoundException('No access allowed.');
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
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserEditType($this->container), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

//        $form->add('submit', 'submit', array(
//            'label' => 'Update',
//            'attr' => array('class' => 'btn btn-primary'),
//        ));

        return $form;
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("MetricsBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if ($entity->getDomain() != $this->get('session')->get('domain')
            && !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
            || !$this->get('security.context')->isGranted($entity->getRole())) {
            throw $this->createNotFoundException('No access allowed.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
            return $this->redirect($this->generateUrl('user'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            if ($entity->getDomain() != $this->get('session')->get('domain')
                && !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
                || !$this->get('security.context')->isGranted($entity->getRole())) {
                throw $this->createNotFoundException('No access allowed.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Get mail to action url to invite new user.
     *
     * @Route("/invite/", name="user_invite")
     * @Method("POST")
     * @return string
     */
    public function getInviteUserAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $params = array();
        $validationTimeFrame = 24;

        if ($request->request->get('id')) {
            $params['userId'] = $request->request->get('id');

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:User')->find($params['userId']);
            $params['firstname']    = $entity->getFirstname();
            $params['lastname']     = $entity->getLastname();
            $params['email']        = $entity->getEmail();
            $params['login']        = $entity->getUsername();
            $params['jobtitle']     = $entity->getJobtitle();
        }


        if ($request->request->get('user_role')) {
            $params['userRole'] = $request->request->get('user_role');
        }

        if ($request->request->get('login_name')) {
            $params['login'] = $request->request->get('login_name');
        }

        if ($request->request->get('firstname')) {
            $params['firstname'] = $request->request->get('firstname');
        }

        if ($request->request->get('lastname')) {
            $params['lastname'] = $request->request->get('lastname');
        }

        if ($request->request->get('email')) {
            $params['email'] = $request->request->get('email');
        }

        $params['domain'] = $this->get('session')->get('domain');

        $tokenService = $this->get('security_token_service');

        $token = $tokenService->create(
            $validationTimeFrame,
            'user_registration',
            $params
        );

        $baseUrl = $this->generateUrl('registration', array('token' => $token), true);

        $platform = $this->container->getParameter('platform_name');

        $subject = 'Invite to ' . $platform;

        $body  = 'Hello';
        if (isset($params['firstname'])) $body .= ' ' . $params['firstname'];
        $body .= ',%0D%0DYou are invited to join ' . $platform . '.
        %0DClick here to register: ' . $baseUrl . '
        %0D%0DPlease note this link is valid for ' . $validationTimeFrame . ' hours.' . '
        %0D%0DBest regards,%0DYour Service Team';

        $ret = 'mailto:';
        if (isset($params['email'])) $ret .= $params['email'];
        $ret .= '?subject=' . $subject . '&body=' . $body;

        return new Response($ret);
    }

}