<?php

namespace RGies\JiraHighEffortWidgetBundle\Controller;

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
 * @Route("/jira_high_effort_widget")
 */
class DefaultController extends Controller
{

    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraHighEffortWidgetBundle-collect-data")
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
        $updateInterval = $request->get('updateInterval');

        // Data cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraHighEffortWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $timeSpendArray = array();
        $totalTimeSpend = 0;
        $response = array();

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($this->_getLoginCredentials());
            $issues = $issueService->search($jql, 0, 10000, ['aggregatetimespent']);

            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->aggregatetimespent) {
                    $timeSpendArray[$issue->key] = $issue->fields->aggregatetimespent;
                    $totalTimeSpend = $totalTimeSpend + $issue->fields->aggregatetimespent;
                }
            }
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        if (count($timeSpendArray)) {
            arsort($timeSpendArray);

            $z=1;
            $response['value'] = '<table>';
            foreach($timeSpendArray as $key=>$value) {
                $response['value'] = $response['value'] . '<tr style="white-space: nowrap;"><td><i class="fa fa-circle"></i> '
                    . $this->_createLink($key, '')
                    . '&nbsp;&nbsp;</td><td>'
                    . '<i class="ion ion-android-stopwatch"></i> <i>' . htmlentities(round($value / 3600,1) . 'h')
                    . '</i></td></tr>';

                if ($z++==5) break;
            }
            $response['value'] = $response['value'] . '</table>';
        }



        if ($issues->getTotal() > $issues->getMaxResults()) {
            $response['warning'] = 'limit of ' . $issues->getMaxResults() . ' issues reached';
        }

        if ($issues->getTotal()) {
            $response['subtext'] = $issues->getTotal() . ' issues analysed / Ø '
                . round($totalTimeSpend / $issues->getTotal() / 3600, 1) . 'h';
        }

        $cache->setValue('JiraHighEffortWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _createLink($key, $text)
    {
        return '<a href="' . $this->getParameter('jira_host') . '/browse/' . $key
        . '" target="_blank" title="' . str_replace('"', '&quot;', $text) . '">' . $key . '</a>';
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
