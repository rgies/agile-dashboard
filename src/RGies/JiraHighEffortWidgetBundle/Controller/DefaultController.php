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

        // Allow php to handle parallel request.
        // Please remove if you need to write something to the session.
        session_write_close();

        $widgetId       = $request->get('id');
        $widgetType     = $request->get('type');
        $updateInterval = $request->get('updateInterval');
        $size           = $request->get('size');

        // Data cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraHighEffortWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $timeSpendArray = array();
        $summaryArray = array();
        $totalTimeSpend = 0;
        $response = array();
        $colSpacer = str_repeat('&nbsp;',5);
        $maxLines = ($size=='1x2'||$size=='2x2') ? 15 : 5;

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($jiraLogin);
            $issues = $issueService->search($jql, 0, 10000, ['aggregatetimespent','summary']);

            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->aggregatetimespent) {
                    $timeSpendArray[$issue->key] = $issue->fields->aggregatetimespent;
                    $totalTimeSpend = $totalTimeSpend + $issue->fields->aggregatetimespent;
                    $summaryArray[$issue->key] = $issue->fields->summary;
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
                $response['value'] .= '<tr style="white-space: nowrap;"><td><i class="fa fa-circle"></i> '
                    . $this->_createLink($key, $summaryArray[$key])
                    . $colSpacer . '</td><td>'
                    . '<i class="ion ion-android-stopwatch"></i> <i>' . htmlentities(round($value / 3600,1) . 'h')
                    . '</i></td>';

                if ($size == '2x1' || $size == '2x2') {
                    $response['value'] .= '<td>' . $colSpacer. $this->_getShortSummery($summaryArray[$key], 50) . '</td>';
                }

                $response['value'] .= '</tr>';

                if ($z++==$maxLines) break;
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

    /**
     * Get shorten summary.
     *
     * @param $summery
     * @param int $len
     * @return string
     */
    protected function _getShortSummery($summery, $len = 30)
    {
        if (strlen($summery) > ($len-1)) {
            $shortName = mb_substr($summery, 0, ($len-1)) . '...';
        } else {
            $shortName = $summery;
        }

        return '<span title="' . str_replace('"', '&quot;', $summery) . '">' . htmlentities($shortName) . '</span>';
    }

    /**
     * Creates link to jira issue.
     *
     * @param $key
     * @param $text
     * @return string
     */
    protected function _createLink($key, $text)
    {
        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();

        return '<a href="' . $jiraLogin->getJiraHost() . '/browse/' . $key
        . '" target="_blank" title="' . str_replace('"', '&quot;', $text) . '">' . $key . '</a>';
    }

}
