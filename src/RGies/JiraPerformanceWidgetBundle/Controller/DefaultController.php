<?php

namespace RGies\JiraPerformanceWidgetBundle\Controller;

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
 * @Route("/jira_performance_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraPerformanceWidgetBundle-collect-data")
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
        if ($cacheValue = $cache->getValue('JiraPerformanceWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $response = array();
        $response['icon'] = $widgetConfig->getIcon();

        $jql = $widgetConfig->getJqlQuery();
        $spendTime = 0;
        $spendStoryPoints = 0;
        $totalCount = 0;
        $issueCount = 0;
        $storyPointField = 'customfield_10004';
        $workDays = array();

        try {
            $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());
            $issues = $issueService->search($jql, 0, 10000, ['updated','aggregatetimespent',$storyPointField]);
            $totalCount = $issues->getTotal();

            foreach ($issues->getIssues() as $issue) {
                if (isset($issue->fields->$storyPointField)) {
                    $issueCount++;
                    $spendStoryPoints += $issue->fields->$storyPointField;

                    if ($issue->fields->aggregatetimespent) {
                        $spendTime += $issue->fields->aggregatetimespent;
                    }
                }

                if ($issue->fields->updated) {
                    $updatedKey = $issue->fields->updated->format('Y-m-d');
                    if (!isset($workDays[$updatedKey])) {
                        $workDays[$updatedKey] = 0;
                    }
                    $workDays[$updatedKey]++;
                }

            }
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
            //$this->createNotFoundException('Search Failed: ' . $e->getMessage());
        }


        if ($issues->getTotal() > $issues->getMaxResults()) {
            $response['warning'] = 'limit of ' . $issues->getMaxResults() . ' issues reached';
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        $response['days'] = count($workDays);
        $response['sum'] = $spendStoryPoints;
        $response['unit'] = 'SP';
        $response['link'] = $this->getParameter('jira_host') . '/issues/?jql=' . urlencode($jql);
        $response['subtext'] = '';

        if (count($workDays)) {
            $response['subtext'] .= count($workDays) . ' days / '
                . round($spendStoryPoints / count($workDays), 1) . ' SP/d<br/>';
        }

        $response['subtext'] .= $issueCount . ' issues';
        if ($issueCount) {
            $response['subtext'] .= ' / Ø ' . round($spendTime / 3600 / $issueCount, 1) . 'h';
        }

        if ($response['sum']) {
            $response['subtext'] .= ' (1SP = ' . round($spendTime / 3600 / $response['sum'], 1) . 'h)';
        }

        $cache->setValue('JiraPerformanceWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
