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
                $endDate = new \DateTime($widgetConfig->getEndDate() . ' 23:59:59');
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

        $data = $this->_getDataArray($widgetId, 1);


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
                $jqlQuery = str_replace('%end%', $now->format('Y-m-d 23:59'), $jqlQuery);

                try {
                    $entity = new WidgetData();
                    $entity->setWidgetId($widgetId);
                    $entity->setDataRow(1);
                    $entity->setDate($keyDate);

                    // search for jira issues from jql
                    $issues = $issueService->search($jqlQuery, 0, 10000, ['key'], ['changelog']);
                    //$entity->setValue($issues->getTotal());



                    foreach ($issues->getIssues() as $issue) {

                        /**
                        if (isset($issue->changelog)) {
                            foreach ($issue->changelog as $changelog) {
                                //if ()
                            }

                        }**/

                        //var_dump($issue); exit;

                        //if ($issue->expand) {
                        //    $spendTime += $issue->fields->aggregatetimespent;
                        //}
                    }
                    //$entity->setValue(round($spendTime / 3600, 0));



                    // don't persists data which are not final
                    if ($now->format('Y-m-d') == date('Y-m-d')) {
                        $cache->setValue('JiraLeadTimeWidgetBundle_today', $widgetId, $entity->getValue());
                    } else {
                        $em->persist($entity);
                        $em->flush();
                    }

                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), 1, $entity->getValue());
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

        $response['days'] = $days;


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
        $dataRepository = $em->getRepository('JiraLeadTimeWidgetBundle:WidgetData');

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
