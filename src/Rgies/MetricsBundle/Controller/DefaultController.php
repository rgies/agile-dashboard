<?php

namespace Rgies\MetricsBundle\Controller;

use Rgies\MetricsBundle\Entity\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{
    /**
     * Website home action.
     *
     * @Route("/id/{id}", name="home")
     * @Template()
     */
    public function indexAction($id = null)
    {
        $em = $this->getDoctrine()->getManager();

        $dashboardEntity = $em->getRepository('MetricsBundle:Dashboard');
        $dashboards = $dashboardEntity->createQueryBuilder('d')
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
        }

        $widgetEntity = $em->getRepository('MetricsBundle:Widgets');
        $widgets = $widgetEntity->createQueryBuilder('w')
            ->where('w.enabled = 1')
            ->orderBy('w.id', 'ASC')
            ->getQuery()->getResult();

        if ($id) {
            $dashboard = $dashboardEntity->find($id);
        } else {
            $dashboard = $dashboards[0];
        }


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
