<?php

namespace RGies\JiraTimeInStateWidgetBundle\Controller;

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
use RGies\JiraTimeInStateWidgetBundle\Entity\WidgetData;

/**
 * Widget controller.
 *
 * @Route("/jira_time_in_state_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraTimeInStateWidgetBundle-collect-data")
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

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraTimeInStateWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $colSpacer = str_repeat('&nbsp;',5);
        $maxLines = ($size == '1x2' || $size == '2x2') ? 15 : 5;

        $response = array();
        $response['history'] = [];

        $colors = $this->getParameter('chart_line_colors');

        $jql = $widgetConfig->getJqlQuery();
        $issueService = new IssueService($jiraLogin);


        // Execute jql query
        try {
            $issues = $issueService->search(
                $jql,
                0,
                100000,
                ['key', 'created'],
                ['changelog']
            );
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        if ($issues->getTotal() > $issues->getMaxResults()) {
            $response['warning'] = 'limit of ' . $issues->getMaxResults() . ' issues reached';
        }

        // evaluate status history
        $this->_buildStatusHistory($response, $issues->getIssues());

        array_multisort($response['states'], SORT_DESC, SORT_NUMERIC);

        $z=0;
        $response['table'] = '<table>';
        foreach ($response['states'] as $state => $value) {
            if ($state) {

                if ($size[0]=='1') {
                    $stateName = $this->_getShortName($state, 20);
                } else {
                    $stateName = $this->_getShortName($state, 28);
                }

                $response['table'] .= '<tr><td><i style="color:'
                    . $colors[$z]
                    . '" class="fa fa-circle"></i> '
                    . $stateName . $colSpacer . '</td>'
                    . '<td>&empty; ' . $this->_formatDuration($value) . $colSpacer . '</td>';

                // additional info on wide layout
                if ($size == '2x1' || $size == '2x2') {
                    if (isset($response['min_time'][$state])) {
                        $response['table']
                            .= '<td><i class="fa fa-arrow-down"></i> '
                            . $this->_formatDuration($response['min_time'][$state]) . $colSpacer
                            . '</td>';
                    }

                    if (isset($response['max_time'][$state])) {
                        $response['table']
                            .= '<td><i class="fa fa-arrow-up"></i> '
                            . $this->_formatDuration($response['max_time'][$state]) . $colSpacer
                            . '</td>';
                    }

                    $response['table']
                        .= '<td>&sum; ' . $response['state_count'][$state] . '</td>';
                }

                $response['table'] .= '</tr>';
                $z++;
            }
            if ($z == $maxLines) break;
        }
        $response['table'] .= '</table>';
        $response['link'] = $jiraLogin->getJiraHost() . '/issues/?jql=' . urlencode($jql);

        // Cache response data
        $cache->setValue('JiraTimeInStateWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Creates issue based array with status history.
     *
     * @param $response
     * @param $issues
     */
    protected function _buildStatusHistory(&$response, $issues)
    {
        $states = [];
        foreach ($issues as $issue) {

            if (isset($issue->changelog) && isset($issue->changelog->histories)) {
                foreach ($issue->changelog->histories as $history) {

                    $created = new \DateTime($history->created);

                    if (isset($history->items))
                    {

                        foreach ($history->items as $item) {

                            if (isset($item->field) && $item->field == 'status') {

                                $timeInState = null;

                                if (!isset($response['history'][$issue->key])) {
                                    $response['history'][$issue->key][] = [
                                        'date'   => $issue->fields->created,
                                        'status' => $item->fromString
                                    ];
                                }

                                $lastItemIndex = count($response['history'][$issue->key])-1;
                                $lastItem = $response['history'][$issue->key][$lastItemIndex];

                                $response['labels'][$item->toString] = $item->toString;

                                $timeInState = ($created->getTimestamp() - $lastItem['date']->getTimestamp()) / 3600;

                                $response['history'][$issue->key][$lastItemIndex]['waiting']
                                    = $timeInState;

                                // increment time in state based on status
                                if ($timeInState >= 0.002) {
                                    if (isset($states[$item->fromString][$issue->key])) {
                                        $states[$item->fromString][$issue->key] += $timeInState;
                                    } else {
                                        $states[$item->fromString][$issue->key] = $timeInState;
                                    }
                                }

                                $response['history'][$issue->key][] = [
                                    'date'    => $created,
                                    'status'  => $item->toString
                                ];

                            }
                        }
                    }
                }
            }
        }

        // calculate average value
        $response['states'] = [];
        foreach ($states as $state=>$values)
        {
            if (count($values)) {
                $response['states'][$state] = array_sum($values) / count($values);
                $response['min_time'][$state] = min($values);
                $response['max_time'][$state] = max($values);
                $response['state_count'][$state] = count($values);
            } else {
                $response['states'][$state] = 0;
                $response['min_time'][$state] = 0;
                $response['max_time'][$state] = 0;
                $response['state_count'][$state] = 0;
            }
        }
    }

    /**
     * Reloads widget data.
     *
     * @Route("/reload-data/", name="JiraTimeInStateWidgetBundle-reload-data")
     * @Method("POST")
     * @return Response
     */
    public function reloadWidgetAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        // Allow php to handle parallel request.
        // Please remove if you need to write something to the session.
        session_write_close();

        $widgetId = $request->get('id');
        $this->get('CacheService')->deleteValue('JiraTimeInStateWidgetBundle', $widgetId);

        return new Response();
    }

    /**
     * Format duration.
     *
     * @param float $value duration
     * @return string
     */
    protected function _formatDuration($value)
    {
        if (!$value) {
            $unit = 'd';
            $duration = 0;
        } elseif ($value >= 24) {
            $unit = 'd';
            $duration = round($value / 24, 1);
        } elseif ($value * 60 < 1) {
            $unit = 's';
            $duration = round($value * 3600, 0);
        } elseif ($value < 1) {
            $unit = 'm';
            $duration = round($value * 60, 1);
        } else {
            $unit = 'h';
            $duration = round($value, 1);
        }

        return $duration . $unit;
    }

    /**
     * Get shorten summary.
     *
     * @param $value
     * @param int $len
     * @return string
     */
    protected function _getShortName($value, $len = 30)
    {
        if (strlen($value) > ($len-1)) {
            $shortName = mb_substr($value, 0, ($len-1)) . '...';
        } else {
            $shortName = $value;
        }

        return '<span title="' . str_replace('"', '&quot;', $value) . '">' . htmlentities($shortName) . '</span>';
    }

}
