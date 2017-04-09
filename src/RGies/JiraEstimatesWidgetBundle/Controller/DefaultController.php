<?php

namespace RGies\JiraEstimatesWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\TimeTracking;
use JiraRestApi\JiraException;


/**
 * Widget controller.
 *
 * @Route("/jira_estimates_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraEstimatesWidgetBundle-collect-data")
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
        $spendTime = 0;
        $totalCount = 0;

        try {
            $issueService = new IssueService($this->_getLoginCredentials());
            $issues = $issueService->search($jql, 0, 10000, ['aggregatetimespent']);
            $totalCount = $issues->getTotal();

            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->aggregatetimespent) {
                    $spendTime += $issue->fields->aggregatetimespent;
                }
            }
        } catch (JiraException $e) {
            $this->createNotFoundException('Search Failed: ' . $e->getMessage());
        }

        $response['value'] = $spendTime;

        if ($issues->getTotal() > $issues->getMaxResults()) {
            $response['warning'] = 'limit of ' . $issues->getMaxResults() . ' issues reached';
        } else {
            $response['subtext'] = $totalCount . ' issues / Ø '
                . round($spendTime / 3600 / $issues->getTotal(), 1) . 'h';
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
}
