<?php

namespace RGies\MetricsBundle\Controller;

use RGies\MetricsBundle\Entity\Dashboard;
use RGies\MetricsBundle\Entity\User;
use RGies\MetricsBundle\Form\UserRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{
    const LAST_VISITED_DASHBOARD = 'last_dashboard_id';

    /**
     * Website home action.
     *
     * @Route("/", name="start")
     * @Route("/id/{id}", name="home")
     * @Template()
     */
    public function indexAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $dashboard = null;

        // if no user exists add default admin account
        $user = $em->getRepository('MetricsBundle:User')->findAll();
        if (!$user) {
            // admin user
            $userAdmin = new User();
            $userAdmin->setUsername('Admin');
            $userAdmin->setPassword('admin');
            $userAdmin->setRole('ROLE_ADMIN');
            $userAdmin->setJobtitle('admin');
            $userAdmin->setFirstname('Admin');
            $userAdmin->setLastname('User');
            $userAdmin->setEmail('admin@xxx.com');
            $userAdmin->setIsActive(true);
            $em->persist($userAdmin);
            $em->flush();

            // setup jira credential if nothing exists
            if (!$this->get('credentialService')->loadCredentials('jira')) {
                return $this->redirect($this->generateUrl('jira_core_widget_login_edit'));
            }
        }

        // get dashboards
        $dashboards = $em->getRepository('MetricsBundle:Dashboard')
                ->createQueryBuilder('d')
                //->where('d.enabled = 1')
                ->orderBy('d.pos', 'ASC')
                ->getQuery()->getResult();

        // create default dashboard
        if (!$dashboards) {
            $dashboard = new Dashboard();
            $dashboard->setTitle('Main Metrics');
            $dashboard->setPos(1);
            $em->persist($dashboard);
            $em->flush();
            $dashboards = array($dashboard);
        } else if ($id) {
            // create cookie
            $expire = new \DateTime('+600 days');
            $cookie = new Cookie(self::LAST_VISITED_DASHBOARD, $id, $expire);
            $response = new Response();
            $response->headers->setCookie($cookie);
            //$response->send();
            $response->sendHeaders();
        } else if ($request->cookies->has(self::LAST_VISITED_DASHBOARD)) {
            $id = $request->cookies->get(self::LAST_VISITED_DASHBOARD);
        } else {
            $dashboard = $dashboards[0];
        }

        if (!$dashboard && $id) {
            $dashboard = $em->getRepository('MetricsBundle:Dashboard')->find($id);
            if (!$dashboard) {
                $result = $em->getRepository('MetricsBundle:Dashboard')->findBy(array(), array('pos'=>'ASC'));
                $dashboard = $result[0];
            }
        }

        // get widgets
        $widgets = $em->getRepository('MetricsBundle:Widgets')
            ->createQueryBuilder('w')
            ->where('w.dashboard = :id')
            ->andWhere('w.enabled = 1')
            ->orderBy('w.pos')
            ->setParameter('id', $dashboard->getId())
            ->getQuery()->getResult();

        return array (
            'interval'      => $this->getParameter('widget_update_interval'),
            'widgets'       => $widgets,
            'dashboards'    => $dashboards,
            'dashboard'     => $dashboard,
        );
    }

    /**
     * Imprint action.
     *
     * @Route("/imprint/", name="imprint")
     * @Template()
     */
    public function imprintAction()
    {
        return array();
    }

    /**
     * Contact action.
     *
     * @Route("/contact/", name="contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $emailTo = $this->container->getParameter('platform_contact');

        if ($request->getMethod() == 'POST')
        {
            $contact = $request->request->all();

            $message = \Swift_Message::newInstance()
                ->setSubject('Contact Request')
                ->setFrom($emailTo)
                ->setTo($emailTo)
                ->setBody($this->renderView('MetricsBundle:Email:contactEmail.txt.twig', array('contact' => $contact)));

            $this->get('mailer')->send($message);
            $this->get('session')->getFlashBag()->add('message', 'Your contact message was successfully sent. Thank you!');
        }

        return array();
    }

    /**
     * About action.
     *
     * @Route("/about/", name="about")
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }

    /**
     * Login action.
     *
     * @Route("/login/", name="login")
     * @Route("/login-auth/", name="login_auth")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        );
    }

    /**
     * User registration.
     *
     * @Route("/register/{token}", name="registration")
     * @Template()
     */
    public function registerAction(Request $request, $token)
    {
        $tokenService = $this->get('security_token_service');
        $params = $tokenService->isValid($token, 'user_registration');
        $em = $this->getDoctrine()->getManager();

        if (!$params)
        {
            throw $this->createNotFoundException('Access not allowed.');
        }

        if (isset($params['userId'])) {
            $entity = $em->getRepository('MetricsBundle:User')->find($params['userId']);
        }
        else {
            $entity = new User();
        }

        $form = $this->createForm(new UserRegisterType($this->container, $params), $entity, array(
            'action' => $this->generateUrl('registration', array('token' => $token)),
            'method' => 'POST',
        ));

        $form->add(
            'submit', 'submit',
            array(
                'label' => 'Register',
                'attr' => array('class' => 'btn btn-primary pull-right'),
            )
        );

        if ($request->request->get('rgies_MetricsBundle_user_register'))
        {
            // add user
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entity->setIsActive(true);

                if (isset($params['userRole'])) {
                    $entity->setRole($params['userRole']);
                }

                try {
                    $em->persist($entity);
                    $em->flush();
                }
                catch(\Doctrine\DBAL\DBALException $e) {
                    $this->get('session')->getFlashBag()->add('message', 'Username or email already exists. Please change username and email.');

                    return array(
                        'entity' => $entity,
                        'form'   => $form->createView(),
                    );
                }

                if (isset($params['activityId'])) {
                    $activityId = $params['activityId'];
                    $activity = $em->getRepository('MetricsBundle:Activity')->find($activityId);

                    if ($activity) {
                        // assign user to activity
                        $activity->getUser()->add($entity);

                        if (isset($params['projectRole'])) {
                            // set user role in assigned activity
                            $activity->addUserRole($entity->getId(), $params['projectRole']);
                        }

                        $em->persist($activity);
                        $em->flush();
                    }
                }

                $session = $request->getSession();
                if (null !== $session)
                {
                    $session->set(SecurityContextInterface::LAST_USERNAME, $entity->getUsername());
                }

                $tokenService->validate($token, 'user_registration');

                return $this->redirect($this->generateUrl('login'));
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/myprofile/", name="myprofile")
     * @Template()
     */
    public function myprofileAction()
    {
        $id = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createForm(new MyProfileType($this->container), $entity, array(
            'action' => $this->generateUrl('myprofile_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $editForm->add('submit', 'submit'
            , array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary pull-right')));

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );

        return array();
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/myprofile/update/", name="myprofile_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Default:myprofile.html.twig")
     */
    public function updateAction(Request $request)
    {
        $id = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createForm(new MyProfileType($this->container), $entity, array(
            'action' => $this->generateUrl('myprofile_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $editForm->add('submit', 'submit', array('label' => 'Update'));

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('home'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Logout action.
     *
     * @Route("/logout/", name="logout")
     * @Template()
     */
    public function logoutAction()
    {
        return array();
    }

}
