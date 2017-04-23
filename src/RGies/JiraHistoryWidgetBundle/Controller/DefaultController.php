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

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();
        $startDate = new \DateTime();
        $endDate = new \DateTime();

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
        $days = $startDate->diff($endDate)->format('%a');

        $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());

        $row = 1;
        foreach ($labels as $label)
        {
            $now = new \DateTime();
            $interval = '-1 day';

            // auto calculate interval
            if ($days > 300) {
                $interval = '-3 month';
                $now = new \DateTime('first day of this month');
            } elseif ($days > 100) {
                $interval = '-1 month';
                $now = new \DateTime('first day of this month');
            } elseif ($days > 10) {
                $interval = '-1 week';
                $now = new \DateTime('last week friday');
            }

            $data = $this->_getDataArray($widgetId, $row);
            $rowKey = 'y' . $row;
            $response['labels'][] = $label;
            $response['keys'][] = $rowKey;
            $jql = $jqls[$row-1];

            for ($now; $now > $startDate; $now->modify($interval))
            {
                $keyDate = new \DateTime($now->format('Y-m-d 12:00:00'));
                $dateTs= $keyDate->getTimestamp();

                if (!isset($data[$dateTs]) && $updateCounter<1) {
                    $updateCounter++;
                    $start = clone $now;
                    $start->modify($interval);
                    $jqlQuery = str_replace('%date%', $now->format('Y-m-d'), $jql);
                    $jqlQuery = str_replace('%start%', $start->format('Y-m-d'), $jqlQuery);
                    $jqlQuery = str_replace('%end%', $now->format('Y-m-d'), $jqlQuery);

                    try {
                        $issues = $issueService->search($jqlQuery, 0, 10000, ['key','created','updated']);

                        $entity = new WidgetData();
                        $entity->setWidgetId($widgetId);
                        $entity->setDataRow($row);
                        $entity->setDate($keyDate);
                        $entity->setValue($issues->getTotal());
                        $em->persist($entity);
                        $em->flush();

                        $this->_addData($response['data'], $keyDate->format('Y-m-d'), $rowKey, $entity->getValue(), $row);
                    } catch (JiraException $e) {
                        $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                        return new Response(json_encode($response), Response::HTTP_OK);
                    }
                } elseif (isset($data[$dateTs])) {
                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), $rowKey, $data[$keyDate->getTimestamp()], $row);
                } else {
                    $response['need-update'] = true;
                }
            }

            $row++;
        }

        $response['data'] = array_reverse(array_values($response['data']));
        $response['days'] = $days;

        // Cache response data
        $cache->setValue('JiraHistoryWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _addData(&$dataSource, $date, $rowKey, $value, $num)
    {
        if (isset($dataSource[$date])) {
            $dataSource[$date][$rowKey] = $value;
        } else {
            //array_unshift($dataSource, [$date => ['date' => $date, $rowKey => $value]]);
            $dataSource[$date] = ['date' => $date, $rowKey => $value];
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

}
