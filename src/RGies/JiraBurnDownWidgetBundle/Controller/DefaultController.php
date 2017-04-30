<?php

namespace RGies\JiraBurnDownWidgetBundle\Controller;

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
use RGies\JiraBurnDownWidgetBundle\Entity\WidgetData;
use RGies\MetricsBundle\Entity\Widgets;


/**
 * Widget controller.
 *
 * @Route("/jira_burn_down_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraBurnDownWidgetBundle-collect-data")
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
            if ($cacheValue = $cache->getValue('JiraBurnDownWidgetBundle', $widgetId, null, $updateInterval)) {
                //return new Response($cacheValue, Response::HTTP_OK);
            }
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();
        $response['rows'] = [];
        $response['labels'] = ['Remaining', 'Ideal'];
        $response['keys'] = ['y1', 'y2'];

        $jql = $widgetConfig->getJqlQuery();
        $calcBase = $widgetConfig->getCalcBase();
        $startDate = new \DateTime($widgetConfig->getStartDate());
        $endDate = new \DateTime($widgetConfig->getEndDate() . ' 23:59:59');
        $days = $startDate->diff($endDate)->format('%a');
        $colors = ['#0b62a4', '#7A92A3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'];
        $storyPointField = 'customfield_10004';

        $widget = $em->getRepository('MetricsBundle:Widgets')->find($widgetId);
        $size = $widget->getSize();
        //var_dump($widget); exit;
        //$scaling = 1 / substr($size, -1);

        // Burn down line
        $response['data'][$startDate->format('Y-m-d')]['date'] = $startDate->format('Y-m-d');
        $response['data'][$startDate->format('Y-m-d')]['y2'] = 70;
        $response['data'][$endDate->format('Y-m-d')]['date'] = $endDate->format('Y-m-d');
        $response['data'][$endDate->format('Y-m-d')]['y2'] = 0;


        $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());

        $row = 1;
        $updateCounter = 0;
        $interval = '-1 day';
        $now = clone $endDate;

        // auto calculate interval
        if ($days > 300) {
            $interval = '-3 month';
        } elseif ($days > 100) {
            $interval = '-1 month';
        } elseif ($days > 30) {
            $interval = '-1 week';
        } elseif ($days > 14) {
            $interval = '-1 week';
        }

        $data = $this->_getDataArray($widgetId, 1);

        for ($now; $now > $startDate; $now->modify($interval))
        {
            $keyDate = new \DateTime($now->format('Y-m-d'));
            $dateTs= $keyDate->getTimestamp();

            if ($dateTs>time()) {
                continue;
            }

            if (!isset($data[$dateTs]) && $updateCounter<1) {
                $updateCounter++;
                $start = clone $now;
                $start->modify($interval);
                $jqlQuery = str_replace('%date%', $now->format('Y-m-d'), $jql);
                $jqlQuery = str_replace('%start%', $start->format('Y-m-d'), $jqlQuery);
                $jqlQuery = str_replace('%end%', $now->format('Y-m-d 23:59'), $jqlQuery);

                try {
                    $issues = $issueService->search($jqlQuery, 0, 10000, ['key','created','updated',$storyPointField,'aggregatetimespent']);

                    $entity = new WidgetData();
                    $entity->setWidgetId($widgetId);
                    $entity->setDataRow($row);
                    $entity->setDate($keyDate);

                    switch($calcBase)
                    {
                        case 'points':
                            $storyPoints = 0;
                            foreach ($issues->getIssues() as $issue) {
                                if (isset($issue->fields->$storyPointField)) {
                                    $storyPoints += $issue->fields->$storyPointField;
                                }
                            }
                            $entity->setValue($storyPoints);
                            break;
                        case 'hours':
                            $estimate = 0;
                            foreach ($issues->getIssues() as $issue) {
                                if ($issue->fields->aggregatetimeestimate) {
                                    $estimate += $issue->fields->aggregatetimeestimate;
                                }
                            }
                            $entity->setValue($estimate / 3600);
                            break;
                        case 'count':
                        default:
                            $entity->setValue($issues->getTotal());
                    }

                    $em->persist($entity);
                    $em->flush();

                    $this->_addData($response['data'], $keyDate->format('Y-m-d'), 'y1', $entity->getValue());
                } catch (JiraException $e) {
                    $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                    return new Response(json_encode($response), Response::HTTP_OK);
                }
            } elseif (isset($data[$dateTs])) {
                $this->_addData($response['data'], $keyDate->format('Y-m-d'), 'y1', $data[$keyDate->getTimestamp()]);
            } else {
                $response['need-update'] = true;
            }
        }

        $response['legend'] = '';
        foreach ($response['labels'] as $key=>$label) {
            $response['legend'] .= '&nbsp;&nbsp;<i style="color:' . $colors[$key] . '" class="fa fa-circle"></i> ' . $label;
        }

        $response['data'] = array_values($response['data']);
        $response['days'] = $days;

        // Cache response data
        $cache->setValue('JiraBurnDownWidgetBundle', $widgetId, json_encode($response));

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
            $dataSource[$date][$rowKey] = $value;
        } else {
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
        $dataRepository = $em->getRepository('JiraBurnDownWidgetBundle:WidgetData');

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
