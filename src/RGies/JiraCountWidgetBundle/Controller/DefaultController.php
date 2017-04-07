<?php

namespace RGies\JiraCountWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\JiraException;

/**
 * Widget controller.
 *
 * @Route("/jira_count_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraCountWidgetBundle-collect-data")
     * @Method("POST")
     * @return Response
     */
    public function collectDataAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $widgetId       = $request->get('id');
        $widgetType     = $request->get('type');
        $widgetConfig   = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $response = array('value'=>'###');
        $response['icon'] = $widgetConfig->getIcon();

        $jql = $widgetConfig->getJqlQuery();
        $issues = $this->_getIssueList($jql);

        if ($issues) {
            $response['value'] = $issues->getTotal();
        }

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * @return ArrayConfiguration
     */
    protected function _getLoginCredentials()
    {
        return new ArrayConfiguration(
            array(
                'jiraHost' => $this->getParameter('jira_host'),
                'jiraUser' => $this->getParameter('jira_user'),
                'jiraPassword' => $this->getParameter('jira_password'),
            )
        );
    }

    /**
     * Gets issue list from given jql.
     *
     * @param string $jql
     * @param int $limit
     * @return \JiraRestApi\Issue\IssueSearchResult|null
     */
    protected function _getIssueList($jql, $limit = 100000)
    {
        $ret = null;

        try {
            $issueService = new IssueService($this->_getLoginCredentials());

            //$ret = $issueService->search($jql, 0, 100, ['null']);
            $ret = $issueService->search($jql, 0, $limit, ['created','resolutiondate']);

            //var_dump($ret);
        } catch (JiraException $e) {
            $this->createNotFoundException('Search Failed: ' . $e->getMessage());
        }

        return $ret;
    }

}
