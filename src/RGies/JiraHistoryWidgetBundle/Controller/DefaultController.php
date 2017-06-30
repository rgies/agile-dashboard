<?php

namespace RGies\JiraHistoryWidgetBundle\Controller;

use RGies\JiraHistoryWidgetBundle\Entity\WidgetData;
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
 * @Route("/jira_history_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraHistoryWidgetBundle-collect-data")
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
            if ($cacheValue = $cache->getValue('JiraHistoryWidgetBundle', $widgetId, null, $updateInterval)) {
                return new Response($cacheValue, Response::HTTP_OK);
            }
        }

        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();
        $startDate = new \DateTime();
        $endDate = new \DateTime();

        $customField = $widgetConfig->getCustomField();
        $labels = explode(',', $widgetConfig->getLabel1());
        $jqls = explode("\n", $widgetConfig->getJqlQuery1());

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
        $days = (int)$startDate->diff($endDate)->format('%a');
        $colors = ['#0b62a4', '#7A92A3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'];

        $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());

        $row = 1;
        foreach ($labels as $label)
        {
            if ($row > count($jqls)) break;

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

            $data = $this->_getDataArray($widgetId, $row);
            $rowKey = 'y' . $row;
            $response['labels'][] = $label;
            $response['keys'][] = $rowKey;

            $jql = $jqls[$row-1];

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
                    && $cacheValue = $cache->getValue('JiraHistoryWidgetBundle_today' . $rowKey, $widgetId, null, 60)) {
                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), $rowKey, $cacheValue);
                } elseif (!isset($data[$dateTs]) && $updateCounter<1) {
                    $updateCounter++;
                    $start = clone $now;
                    $start->modify($interval);
                    $jqlQuery = str_replace('%date%', $now->format('Y-m-d'), $jql);
                    $jqlQuery = str_replace('%start%', $start->format('Y-m-d'), $jqlQuery);
                    $jqlQuery = str_replace('%end%', $now->format('Y-m-d'), $jqlQuery);

                    try {
                        $entity = new WidgetData();
                        $entity->setWidgetId($widgetId);
                        $entity->setDataRow($row);
                        $entity->setDate($keyDate);

                        $response['jql'] = $jqlQuery;

                        switch($widgetConfig->getDataSource())
                        {
                            case 'SpendTime':
                                $spendTime = 0;
                                $issues = $issueService->search($jqlQuery, 0, 10000, ['aggregatetimespent']);
                                foreach ($issues->getIssues() as $issue) {
                                    if ($issue->fields->aggregatetimespent) {
                                        $spendTime += $issue->fields->aggregatetimespent;
                                    }
                                }
                                $entity->setValue(round($spendTime / 3600, 0));
                                break;

                            case 'Custom':
                                $value = 0;
                                $issues = $issueService->search($jqlQuery, 0, 10000, [$customField]);
                                foreach ($issues->getIssues() as $issue) {
                                    if (isset($issue->fields->$customField)) {
                                        $value += $issue->fields->$customField;
                                    }
                                }
                                $entity->setValue($value);
                                break;

                            default:
                                $issues = $issueService->search($jqlQuery, 0, 10000, ['key']);
                                $entity->setValue($issues->getTotal());
                        }


                        // don't persists data which are not final
                        if ($now->format('Y-m-d') == date('Y-m-d')) {
                            $cache->setValue('JiraHistoryWidgetBundle_today' . $rowKey, $widgetId, $entity->getValue());
                        } else {
                            $em->persist($entity);
                            $em->flush();
                        }

                        $this->_addData($response['data'], $keyDate->format('Y-m-d'), $rowKey, $entity->getValue());
                    } catch (JiraException $e) {
                        $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                        return new Response(json_encode($response), Response::HTTP_OK);
                    }
                } elseif (isset($data[$dateTs])) {
                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), $rowKey, $data[$keyDate->getTimestamp()]);
                } else {
                    $response['need-update'] = true;
                }
            }

            $row++;
        }

        $response['legend'] = '';
        foreach ($response['labels'] as $key=>$label) {

            $response['legend'] .= '&nbsp;&nbsp;<i style="color:'
                . $colors[$key]
                . '" class="fa fa-circle"></i> '
                . '<span>' . $label . '</span>';
        }

        $response['data'] = array_reverse(array_values($response['data']));
        $response['days'] = $days;

        // Cache response data
        $cache->setValue('JiraHistoryWidgetBundle', $widgetId, json_encode($response));

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
        $dataRepository = $em->getRepository('JiraHistoryWidgetBundle:WidgetData');

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
                $result[$entity->getDate()->getTimestamp()] = $entity->getValue();
            }
        }

        return $result;
    }

    /**
     * Reloads widget data.
     *
     * @Route("/reload-data/", name="JiraHistoryWidgetBundle-reload-data")
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

        $this->get('CacheService')->deleteValue('JiraHistoryWidgetBundle', $widgetId);

        // clear widget data cache
        $em->createQuery('delete from JiraHistoryWidgetBundle:WidgetData st where st.widget_id = :id')
            ->setParameter('id', $widgetId)
            ->execute();

        return new Response();
    }

}
