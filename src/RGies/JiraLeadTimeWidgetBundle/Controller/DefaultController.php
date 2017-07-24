<?php

namespace RGies\JiraLeadTimeWidgetBundle\Controller;

use RGies\JiraLeadTimeWidgetBundle\Entity\WidgetData;
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
 * @Route("/jira_lead_time_widget")
 */
class DefaultController extends Controller
{
    protected $_widgetId;

    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraLeadTimeWidgetBundle-collect-data")
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
            if ($cacheValue = $cache->getValue('JiraLeadTimeWidgetBundle', $widgetId, null, $updateInterval)) {
                return new Response($cacheValue, Response::HTTP_OK);
            }
        }

        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();

        $this->_widgetId = $widgetId;

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
        $days = (int)$startDate->diff($endDate)->format('%a');

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

        $data = $this->_getDataArray(1);

        for ($now; $now > $startDate; $now->modify($interval))
        {
            $keyDate = new \DateTime($now->format('Y-m-d'));
            $dateTs= $keyDate->getTimestamp();

            // jump over days in future
            if ($dateTs>time()) {
                continue;
            }

            // check for not persisted data in cache
            if ($now->format('Y-m-d') == date('Y-m-d')
                && $cacheValue = $cache->getValue('JiraLeadTimeWidgetBundle_today', $widgetId, null, 60)) {
                $this->_addData($response['data'], $keyDate->format('Y-m-d'), 1, $cacheValue);
            } elseif (!isset($data[$dateTs]) && $updateCounter<1) {
                $updateCounter++;
                $start = clone $now;
                $start->modify($interval);
                $jqlQuery = str_replace('%date%', $now->format('Y-m-d'), $jql);
                $jqlQuery = str_replace('%start%', $start->format('Y-m-d'), $jqlQuery);
                $jqlQuery = str_replace('%end%', $now->format('Y-m-d'), $jqlQuery);

                try {
                    // search for jira issues from jql
                    $issues = $issueService->search($jqlQuery, 0, 10000, ['key', 'created', 'resolutiondate']);
                    $response['jql'] = $jqlQuery;
                    $response['leadTime'] = [];

                    $leadTimeArray = array();
                    $average = 0;
                    $min = 0;
                    $max = 0;

                    foreach ($issues->getIssues() as $issue) {

                        if (isset($issue->fields->resolutiondate) && $issue->fields->resolutiondate) {
                            $resolved = new \DateTime($issue->fields->resolutiondate);
                            $leadTimeDays = ($resolved->getTimestamp() - $issue->fields->created->getTimestamp()) / 3600 / 24;
                            $response['leadTime'][$issue->key] = $leadTimeDays;

                            $leadTimeArray[] = $leadTimeDays;
                        }

                    }

                    if (count($leadTimeArray)) {
                        $average = array_sum($leadTimeArray) / count($leadTimeArray);
                        $min = min($leadTimeArray);
                        $max = max($leadTimeArray);
                    }

                    // don't persists data which are not final
                    if ($now->format('Y-m-d') == date('Y-m-d')) {
                        $cache->setValue('JiraLeadTimeWidgetBundle_today', $widgetId, $average);
                    } else {
                        $this->_saveData($keyDate, 1, $average);
                        $this->_saveData($keyDate, 2, $min);
                        $this->_saveData($keyDate, 3, $max);
                    }

                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), 1, $average);
                } catch (JiraException $e) {
                    $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                    return new Response(json_encode($response), Response::HTTP_OK);
                }
            } elseif (isset($data[$dateTs])) {
                $this->_addData($response['data'], $keyDate->format('Y-m-d'), 1, $data[$keyDate->getTimestamp()]);
            } else {
                $response['need-update'] = true;
            }
        }

        $response['startdate'] = $startDate->format('y-m-d');
        $response['enddate'] = $endDate->format('y-m-d');
        $response['days'] = $days;
        $response['value'] = '###';
        $response['subtext'] = $response['startdate'] . ' - ' . $response['enddate'];

        // calculate average
        if (count($response['data'])) {
            $leadTimeValues = array_filter(
                array_column($response['data'], 1),
                function ($val){return $val!=0;}
            );

            if (count($leadTimeValues)) {
                $response['value'] = round(array_sum($leadTimeValues) / count($leadTimeValues), 1);

                $minValues = $this->_getDataArray(2);
                $maxValues = $this->_getDataArray(3);

                if (count($minValues) && count($maxValues)) {
                    $response['min'] = round(min($minValues), 1);
                    $response['max'] = round(max($maxValues), 1);

                    $response['subtext'] .= '&nbsp;&nbsp;&nbsp;<i class="fa fa-arrow-down"></i>'
                        . $response['min'] . 'd / <i class="fa fa-arrow-up"></i>'
                        . $response['max'] . 'd';
                }
            }

            $response['leadTimeValues'] = array_reverse(
                array_map('round', $leadTimeValues, array_fill(0, count($leadTimeValues), 1))
            );
            $response['leadTimeLabels'] = array_reverse(array_column($response['data'], 'date'));
        }

        // Cache response data
        $cache->setValue('JiraLeadTimeWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Add new entry to data array.
     *
     * @param array $dataSource Reference to data source array.
     * @param string $date Date of new dataset
     * @param string $rowKey Data row key
     * @param integer $value Data value
     */
    protected function _addData(&$dataSource, $date, $rowKey, $value)
    {
        if (isset($dataSource[$date])) {
            $dataSource[$date][$rowKey] = (float)$value;
        } else {
            $dataSource[$date] = ['date' => $date, $rowKey => (float)$value];
        }
    }

    /**
     * Save data value.
     *
     * @param $date
     * @param $rowKey
     * @param $value
     */
    protected function _saveData($date, $rowKey, $value)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new WidgetData();
        $entity->setWidgetId($this->_widgetId);
        $entity->setDataRow($rowKey);
        $entity->setDate($date);

        $entity->setValue($value);

        $em->persist($entity);
        $em->flush();
    }

    /**
     * Gets stored graph data.
     *
     * @param integer $widgetId Widget id
     * @param integer $dataRowId Data row id
     * @return array Data
     */
    protected function _getDataArray($dataRowId)
    {
        $result = array();

        $em = $this->getDoctrine()->getManager();
        $dataRepository = $em->getRepository('JiraLeadTimeWidgetBundle:WidgetData');

        $data = $dataRepository
            ->createQueryBuilder('d')
            ->where('d.widget_id = :id')
            ->andWhere('d.data_row = :row')
            //->orderBy('d.date', 'ASC')
            ->setParameter('id', $this->_widgetId)
            ->setParameter('row', $dataRowId)
            ->getQuery()->getResult();

        if ($data) {
            foreach ($data as $entity) {
                $result[$entity->getDate()->getTimestamp()] = $entity->getValue();
            }
        }

        return $result;
    }
}
