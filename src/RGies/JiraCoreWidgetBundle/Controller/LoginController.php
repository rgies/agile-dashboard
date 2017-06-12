<?php

namespace RGies\JiraCoreWidgetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\JiraCoreWidgetBundle\Entity\Login;
use RGies\JiraCoreWidgetBundle\Form\LoginType;

/**
 * Login controller.
 *
 * @Route("/jira_core_widget_login")
 */
class LoginController extends Controller
{
    protected function _getCredentialObject()
    {
        $entity = null;
        $this->_errorMessage = null;

        try {
            $entity = $this->get('CredentialService')->loadCredentials('jira');
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }

        if (!$entity) {
            $entity = new \stdClass();
            $entity->host = '';
            $entity->user = '';
            $entity->password = '';
        }

        return $entity;
    }

    /**
     * Creates a new Login entity.
     *
     * @Route("/", name="jira_core_widget_login_create")
     * @Method("POST")
     * @Template("JiraCoreWidgetBundle:Login:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = $this->_getCredentialObject();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('CredentialService')->saveCredentials(
                'jira',
                $entity
            );

            return $this->redirect($this->generateUrl('provider'));
        }

        /*
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('login'));
        }*/

        return array(
            'entity'  => $entity,
            'form'    => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Login entity.
     *
     * @param $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm($entity)
    {
        $form = $this->createForm(new LoginType(), $entity, array(
            'action' => $this->generateUrl('jira_core_widget_login_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Login entity.
     *
     * @Route("/edit", name="jira_core_widget_login_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $entity = $this->_getCredentialObject();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity'  => $entity,
            'form'    => $form->createView(),
        );
    }

}
