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
        $needUpdate     = $request->get('needUpdate');

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($needUpdate === null) {
            if ($cacheValue = $cache->getValue('JiraTimeInStateWidgetBundle', $widgetId, null, $updateInterval)) {
                //return new Response($cacheValue, Response::HTTP_OK);
            }
        }

        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();
        $startDate = new \DateTime();
        $endDate = new \DateTime();

        $jql = $widgetConfig->getJqlQuery();

        if ($widgetConfig->getStartDate()) {
            try {
                $startDate = new \DateTime($widgetConfig->getStartDate());
            } catch (Exception $e)
            {
                $response['warning'] = wordwrap('Wrong start date format: ' . $e->getMessage(), 38, '<br/>');
                return new Response(json_encode($response), Response::HTTP_OK);
            }
        }

        if ($widgetConfig->getEndDate()) {
            try {
                $endDate = new \DateTime($widgetConfig->getEndDate());
            } catch (Exception $e)
            {
                $response['warning'] = wordwrap('Wrong end date format: ' . $e->getMessage(), 38, '<br/>');
                return new Response(json_encode($response), Response::HTTP_OK);
            }
        }

        $updateCounter = 0;
        $response['rows'] = [];
        $response['labels'] = [];
        $response['keys'] = [];
        $response['history'] = [];
        $days = (int)$startDate->diff($endDate)->format('%a');
        $colors = $this->getParameter('chart_line_colors');

        $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());

        $now = clone $endDate;
        $interval = '-1 day';

        // auto calculate interval
        if ($days > 300) {
            $interval = '-3 month';
            if (!$widgetConfig->getEndDate()) {
                $now = new \DateTime('first day of this month');
            }
        } elseif ($days > 100) {
            $interval = '-1 month';
            if (!$widgetConfig->getEndDate()) {
                $now = new \DateTime('first day of this month');
            }
        } elseif ($days > 14) {
            $interval = '-1 week';
            if (!$widgetConfig->getEndDate()) {
                $now = new \DateTime('last week sunday');
            }
        }

        $row = 1;

        for ($now; $now > $startDate; $now->modify($interval))
        {
            $data = $this->_getDataArray($widgetId, 1);
            $response['labels'] = $this->_getDataArray($widgetId, 2);
            $response['keys'] = array_keys($response['labels']);
            $keyDate = new \DateTime($now->format('Y-m-d'));
            $dateTs= $keyDate->getTimestamp();

            // jump over days in future
            if ($dateTs>time()) {
                continue;
            }

            // check for not persisted data in cache
            if (!isset($data[$dateTs]) && $updateCounter < 1) {

                $updateCounter++;
                $start = clone $now;
                $start->modify($interval);
                $jqlQuery = str_replace('%date%', $now->format('Y-m-d'), $jql);
                $jqlQuery = str_replace('%start%', $start->format('Y-m-d'), $jqlQuery);
                $jqlQuery = str_replace('%end%', $now->format('Y-m-d'), $jqlQuery);

                // Execute jql query
                $issues = $issueService->search(
                    $jqlQuery,
                    0,
                    10000,
                    ['key', 'created'],
                    ['changelog']
                );

                // evaluate status history
                $this->_buildStatusHistory($response, $issues->getIssues());
                $value = array_merge (['date'=>$keyDate->format('Y-m-d')], $response['states']);

                // save data sets
                $entity = new WidgetData();
                $entity->setWidgetId($widgetId)
                    ->setDataRow(1)
                    ->setDate($keyDate);
                $entity->setValue($value);
                $em->persist($entity);
                $em->flush();

                // save labels
                $entity = new WidgetData();
                $entity->setWidgetId($widgetId)
                    ->setDataRow(2)
                    ->setDate(null)
                    ->setValue($response['labels']);
                $em->persist($entity);
                $em->flush();

                $response['data'][$keyDate->format('Y-m-d')] = $value;

            } elseif (isset($data[$dateTs])) {
                $response['data'][$keyDate->format('Y-m-d')] = $data[$keyDate->getTimestamp()];
            } else {
                $response['need-update'] = true;
            }
        }

        // set response legend
        $response['legend'] = '';
        $response['labels'] = array_values($response['labels']);

        foreach ($response['labels'] as $key=>$label) {

            $response['legend'] .= '&nbsp;&nbsp;<i style="color:'
                . $colors[$key]
                . '" class="fa fa-circle"></i> '
                . '<span>' . $label . '</span>';
        }

        // set response data
        //$response['data'] = $response['data'];
        $response['data'] = array_reverse(array_values($response['data']));

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
                                //var_dump($history->items);

                                $timeInState = null;

                                if (!isset($response['history'][$issue->key])) {
                                    $response['history'][$issue->key][] = [
                                        'date'   => $issue->fields->created,
                                        'status' => $item->fromString
                                    ];

                                    $response['labels'][$item->fromString] = $item->fromString;
                                }


                                $lastItemIndex = count($response['history'][$issue->key])-1;
                                $lastItem = $response['history'][$issue->key][$lastItemIndex];

                                $response['labels'][$item->toString] = $item->toString;
                                //$timeDiff = $lastItem['date']->diff($created);
                                //$timeInState = $timeDiff->h + ($timeDiff->days * 24);

                                $timeInState = ($created->getTimestamp() - $lastItem['date']->getTimestamp()) / 3600;


                                //$timeInState = (int)$lastItem['date']->diff($created)->format('%h');
                                $response['history'][$issue->key][$lastItemIndex]['waiting']
                                    = $timeInState;

                                // increment time in state based on status
                                if (isset($states[$item->fromString][$issue->key])) {
                                    $states[$item->fromString][$issue->key] += $timeInState;
                                } else {
                                    $states[$item->fromString][$issue->key] = $timeInState;
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
                $response['states'][$state] = round(array_sum($values) / count($values) / 24,1);
            } else {
                $response['states'][$state] = 0;
            }
        }


    }

    /**
     * Gets stored graph data.
     *
     * @param integer $widgetId Widget id
     * @param integer $dataRowId Data row id
     * @return array Data
     */
    protected function _getDataArray($widgetId, $dataRowId)
    {
        $result = array();

        $em = $this->getDoctrine()->getManager();
        $dataRepository = $em->getRepository('JiraTimeInStateWidgetBundle:WidgetData');

        $data = $dataRepository
            ->createQueryBuilder('d')
            ->where('d.widget_id = :id')
            ->andWhere('d.data_row = :row')
            //->orderBy('d.date', 'ASC')
            ->setParameter('id', $widgetId)
            ->setParameter('row', $dataRowId)
            ->getQuery()->getResult();

        if ($data) {
            foreach ($data as $entity) {
                if ($entity->getDate()) {
                    $result[$entity->getDate()->getTimestamp()] = $entity->getValue();
                } else {
                    $result = $entity->getValue();
                }
            }
        }

        return $result;
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

        $em = $this->getDoctrine()->getManager();

        $widgetId = $request->get('id');

        $this->get('CacheService')->deleteValue('JiraTimeInStateWidgetBundle', $widgetId);

        // clear widget data cache
        $em->createQuery('delete from JiraTimeInStateWidgetBundle:WidgetData st where st.widget_id = :id')
            ->setParameter('id', $widgetId)
            ->execute();

        return new Response();
    }
}
