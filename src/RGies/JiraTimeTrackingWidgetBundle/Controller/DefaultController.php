<?php

namespace RGies\JiraTimeTrackingWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
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
 * @Route("/jira_time_tracking_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraTimeTrackingWidgetBundle-collect-data")
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

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraTimeTrackingWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $response = array();
        $userWorklog = array();
        $trackedDays = array();
        $totalTimeSpend = 0;
        $startDate = new \DateTime('-5 years');
        $endDate = new \DateTime();

        $jql = $widgetConfig->getJqlQuery();


        if ($widgetConfig->getStartDate()) {
            try {
                $startDate = new \DateTime($widgetConfig->getStartDate() . ' 00:00:00');
            } catch (Exception $e)
            {
                $response['warning'] = wordwrap('Wrong start date format: ' . $e->getMessage(), 38, '<br/>');
                return new Response(json_encode($response), Response::HTTP_OK);
            }
        }

        if ($widgetConfig->getEndDate()) {
            try {
                $endDate = new \DateTime($widgetConfig->getEndDate() . ' 23:59:59');
            } catch (Exception $e)
            {
                $response['warning'] = wordwrap('Wrong end date format: ' . $e->getMessage(), 38, '<br/>');
                return new Response(json_encode($response), Response::HTTP_OK);
            }
        }

        $jqlQuery = str_replace('%date%', $startDate->format('Y-m-d'), $jql);
        $jqlQuery = str_replace('%start%', $startDate->format('Y-m-d'), $jqlQuery);
        $jqlQuery = str_replace('%end%', $endDate->format('Y-m-d 23:59'), $jqlQuery);

        try {
            $issueService = new IssueService($jiraLogin);
            $issues = $issueService->search($jqlQuery, 0, 10000, ['key','created','updated','worklog']);

            // loop to found issues
            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->worklog && $issue->fields->worklog->worklogs) {

                    $worklogs = $issue->fields->worklog->worklogs;

                    if ($issue->fields->worklog->total > $issue->fields->worklog->maxResults) {
                        $worklogs = $issueService->getWorklog($issue->key)->getWorklogs();

                        //$response['warning'] = $issue->key . ': to many worklog entries (' . $issue->fields->worklog->total . ')';
                        //return new Response(json_encode($response), Response::HTTP_OK);
                    }

                    foreach ($worklogs as $worklog) {

                        $logdate = new \DateTime($worklog->updated);
                        $dateKey = $logdate->format('d-m-Y');

                        if ($logdate >= $startDate && $logdate <= $endDate) {
                            if (isset($trackedDays[$dateKey])) {
                                $trackedDays[$dateKey] += $worklog->timeSpentSeconds;
                            } else {
                                $trackedDays[$dateKey] = $worklog->timeSpentSeconds;
                            }

                            $this->_updateWorklogItem($userWorklog, $worklog);
                            $totalTimeSpend += $worklog->timeSpentSeconds;
                        }
                    }
                }
            }
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        $response['link'] = $jiraLogin->getJiraHost() . '/issues/?jql=' . urlencode($jql);
        $response['issuecount'] = $issues->getTotal();
        $response['startdate'] = $startDate->format('d-m-Y');
        $response['enddate'] = $endDate->format('d-m-Y');
        $response['usercount'] = count($userWorklog);
        $response['value'] = $totalTimeSpend;
        $response['subtext'] = '<i class="fa fa-user"></i> ' . count($userWorklog) . ' user'
            . ' / ' . count($trackedDays) . ' days';

        if (count($trackedDays) && count($userWorklog)) {
            $response['subtext'] .= ' / ' . round(($totalTimeSpend/36) / (8 * count($trackedDays)
                        * count($userWorklog)),1) . '%';

        }

        $response['subtext'] .= '<br/>' . $response['startdate'] . ' - ' . $response['enddate'];

        // Cache response data
        $cache->setValue('JiraTimeTrackingWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Updates worklog item.
     *
     * @param $storage
     * @param $worklog
     */
    protected function _updateWorklogItem(&$storage, $worklog)
    {
        if ($worklog->updateAuthor) {
            $userData = $worklog->updateAuthor;
        } elseif ($worklog->author) {
            $userData = $worklog->author;
        } else {
            return;
        }

        $userKey = $userData->key;
        $userName = $userData->displayName;

        if (isset($storage[$userKey])) {
            $storage[$userKey]['timespend'] += $worklog->timeSpentSeconds;
        } else {
            $storage[$userKey] = array(
                'name' =>  $userName,
                'timespend' => $worklog->timeSpentSeconds
            );
        }
    }

}
