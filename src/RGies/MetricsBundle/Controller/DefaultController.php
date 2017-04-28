<?php

namespace RGies\MetricsBundle\Controller;

use RGies\MetricsBundle\Entity\Dashboard;
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
            $response->send();
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
