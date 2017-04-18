<?php

namespace RGies\JiraTimeTrackingWidgetBundle\Controller;

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

        $widgetId       = $request->get('id');
        $widgetType     = $request->get('type');

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraTimeTrackingWidgetBundle', $widgetId)) {
            //return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);


        $response = array();
        $userWorklog = array();
        $trackedDays = array();
        $totalTimeSpend = 0;
        $startDate = time();

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($this->_getLoginCredentials());
            $issues = $issueService->search($jql, 0, 10000, ['key','updated','worklog']);

            foreach ($issues->getIssues() as $issue) {
                //var_dump($issue->fields->updated->getTimestamp()); exit;
                $timestamp = $issue->fields->updated->getTimestamp();
                if ($timestamp<$startDate) {
                    $startDate = $timestamp;
                }
            }

            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->worklog && $issue->fields->worklog->worklogs) {
                    //var_dump($issue->fields->worklog); exit;

                    if ($issue->fields->worklog->total > $issue->fields->worklog->maxResults) {
                        $response['warning'] = 'To many worklog entries';
                        return new Response(json_encode($response), Response::HTTP_OK);
                    }

                    foreach ($issue->fields->worklog->worklogs as $worklog) {
                        //var_dump($worklog); exit;

                        $logdate = substr($worklog->updated,0,10);

                        if (strtotime($logdate)>=$startDate) {
                            if (isset($trackedDays[$logdate])) {
                                $trackedDays[$logdate] += $worklog->timeSpentSeconds;
                            } else {
                                $trackedDays[$logdate] = $worklog->timeSpentSeconds;
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

        $response['startdate'] = date('d-m-Y', $startDate);
        $response['usercount'] = count($userWorklog);
        $response['value'] = $totalTimeSpend;
        $response['subtext'] = '<i class="fa fa-user"></i> ' . count($userWorklog) . ' user'
            . ' / ' . count($trackedDays) . ' days ';

        // Cache response data
        $cache->setValue('JiraTimeTrackingWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _updateWorklogItem(&$storage, $worklog)
    {
        if ($worklog->updateAuthor) {
            $userData = $worklog->updateAuthor;
        } elseif ($worklog->author) {
            $userData = $worklog->author;
        } else {
            echo 'NO USER DATA';
            var_dump($worklog); exit;
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
