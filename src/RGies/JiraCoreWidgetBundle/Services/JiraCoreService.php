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
    private $_config;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $config)
    {
        $this->_doctrine    = $doctrine;
        $this->_config      = $config;
    }

    /**
     * Get Jira login credentials.
     *
     * @return ArrayConfiguration Jira login credentials
     */
    public function getLoginCredentials()
    {
        return new ArrayConfiguration(
            array(
                'jiraHost' => $this->_config->getParameter('jira_host'),
                'jiraUser' => $this->_config->getParameter('jira_user'),
                'jiraPassword' => $this->_config->getParameter('jira_password'),
            )
        );
    }

}