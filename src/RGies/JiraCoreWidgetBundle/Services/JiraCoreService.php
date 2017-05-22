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
     * @return ArrayConfiguration Jira login credentials
     */
    public function getLoginCredentials()
    {
        $credentials = $this->_credentialService->loadCredentials('jira');

        if ($credentials) {
            return new ArrayConfiguration(
                array(
                    'jiraHost' => $credentials->host,
                    'jiraUser' => $credentials->user,
                    'jiraPassword' => $credentials->password,
                )
            );
        }

        return null;
    }

}