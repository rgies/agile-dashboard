<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 10.04.2015
 * Time: 13:40
 */

namespace RGies\MetricsBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthenticationHandler extends ContainerAware implements AuthenticationSuccessHandlerInterface
{
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // set session variable for first login
        if ($token->getUser()->getLastLoginDate() == null)
        {
            $request->getSession()->set('firstLogin', true);
        }

        $date = new \DateTime();

        $token->getUser()->setLastLoginDate($date);
        $this->container->get('doctrine')->getManager()->flush();

        // write login data to log file
        $log = '[' . $date->format('Y-m-d H:i:s') . '] ' . $token->getUser()->getUsername() . ' / ' .  $_SERVER['REMOTE_ADDR'];
        file_put_contents('../app/logs/login.log', $log . PHP_EOL, FILE_APPEND);


        $redirectUrl = $request->getSession()->get('_security.secured_area.target_path');

        if ($redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        return new RedirectResponse($this->container->get('router')->generate('init'));
    }
}