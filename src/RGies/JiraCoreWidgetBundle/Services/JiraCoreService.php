<?php
/**
 * Jirorea C service.
 */

namespace RGies\JiraCoreWidgetBundle\Services;

//use RGies\JiraCoreWidgetBundle\Entity\Config;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\TimeTracking;
use JiraRestApi\JiraException;


/**
 * Class WidgetService.
 *
 * @package RGies\JiraCoreWidgetBundle\Services
 */
class JiraCoreService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var array Plugin configuration
     */
    private $_credentialService;

    /**
     * @var ArrayConfiguration Cached credentials.
     */
    private static $_credentials;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $credentialService)
    {
        $this->_doctrine = $doctrine;
        $this->_credentialService = $credentialService;
    }

    /**
     * Get Jira login credentials.
     *
     * @return null|ArrayConfiguration Jira login credentials
     */
    public function getLoginCredentials()
    {
        // cache for request lifetime
        if (self::$_credentials) return self::$_credentials;

        $login = null;
        $credentials = $this->_credentialService->loadCredentials('jira');

        if ($credentials) {
            $login = new ArrayConfiguration(
                array(
                    'jiraHost'      => $credentials->host,
                    'jiraUser'      => $credentials->user,
                    'jiraPassword'  => $credentials->password,
                    'jiraLogFile'   => '../app/logs/jira-rest-client.log',
                    //'jiraLogLevel'  => 'INFO',
                    //'curlOptSslVerifyHost' => true,
                    //'curlOptSslVerifyPeer' => true,
                    //'curlOptVerbose' => true,
                )
            );

            self::$_credentials = $login;
        }

        return $login;
    }

}