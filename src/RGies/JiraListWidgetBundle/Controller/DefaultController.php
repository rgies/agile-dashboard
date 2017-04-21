<?php

namespace RGies\JiraListWidgetBundle\Controller;

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
 * @Route("/jira_list_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraListWidgetBundle-collect-data")
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

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraListWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $response = array();

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());
            $issues = $issueService->search($jql, 0, 5, ['key','summary','created','resolutiondate','aggregatetimespent']);
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        $value = 'NO ITEMS FOUND';
        if ($issues) {
            $value = '<table>';

            foreach ($issues->getIssues() as $issue) {
                $value = $value . '<tr style="white-space: nowrap;"><td><i class="fa fa-circle"></i> '
                    . $this->_createLink($issue->key,$issue->fields->summary)
                    . '&nbsp;&nbsp;</td>';

                // calc issue age
                $issueAge = ($issue->fields->resolutiondate) ? new \DateTime($issue->fields->resolutiondate) : new \DateTime();
                $issueAge = $issueAge->diff($issue->fields->created);
                $issueAgeDays = $issueAge->format('%a');

                $timeSpend = '0h';
                if ($issue->fields->aggregatetimespent) {
                    if ($issue->fields->aggregatetimespent / 3600 > 600) {
                        $timeSpend = round($issue->fields->aggregatetimespent / 3600 / 8,1) . 'd';
                    } else {
                        $timeSpend = round($issue->fields->aggregatetimespent / 3600,1) . 'h';
                    }
                }

                switch ($widgetConfig->getExtendedInfo())
                {
                    case 'age_invest':
                        $value .= '<td><i class="fa fa-coffee"></i> ' . $issueAgeDays . 'd</td>'
                            . '<td>&nbsp;&nbsp;<i class="ion ion-android-stopwatch"></i> ' . $timeSpend . '</td>';
                        break;

                    case 'summery':
                    default:
                        $value .= '<td><i><span title="' . str_replace('"', '&quot;', $issue->fields->summary)
                            . '">' . htmlentities(substr($issue->fields->summary, 0, 18))
                            . '...</span></i></td>';
                        break;
                }

                $value .= '</tr>';
            }
            $value .= '</table>';
        }

        $response['link'] = $this->getParameter('jira_host') . '/issues/?jql=' . urlencode($jql);
        $response['total'] = $issues->getTotal();
        $response['value'] = $value;

        // Cache response data
        $cache->setValue('JiraListWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _createLink($key, $text)
    {
        return '<a href="' . $this->getParameter('jira_host') . '/browse/' . $key
            . '" target="_blank" title="' . str_replace('"', '&quot;', $text) . '">' . $key . '</a>';
    }
}
