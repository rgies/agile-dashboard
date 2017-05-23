<?php

namespace RGies\JiraSpendTimeWidgetBundle\Controller;

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
     * @Route("/collect-data/", name="JiraSpendTimeWidgetBundle-collect-data")
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

        // Data cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraSpendTimeWidget', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $response = array();
        $response['icon'] = $widgetConfig->getIcon();

        $jql = $widgetConfig->getJqlQuery();

        $spendTime = 0;
        $totalCount = 0;

        try {
            $issueService = new IssueService($jiraLogin);
            $issues = $issueService->search($jql, 0, 10000, ['aggregatetimespent']);
            $totalCount = $issues->getTotal();

            foreach ($issues->getIssues() as $issue) {

                if ($issue->fields->aggregatetimespent) {
                    $spendTime += $issue->fields->aggregatetimespent;
                }
            }
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
            //$this->createNotFoundException('Search Failed: ' . $e->getMessage());
        }

        $response['value'] = $spendTime;

        if ($issues->getTotal() > $issues->getMaxResults()) {
            $response['warning'] = 'limit of ' . $issues->getMaxResults() . ' issues reached';
        } else {
            $response['subtext'] = $totalCount . ' issues / Ø '
                . round($spendTime / 3600 / $issues->getTotal(), 1) . 'h';
        }

        $response['link'] = $jiraLogin->getJiraHost() . '/issues/?jql=' . urlencode($jql);

        $cache->setValue('JiraSpendTimeWidget', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
