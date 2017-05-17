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
                return new Response($cacheValue, Response::HTTP_OK);
            }
        }

        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $response['data'] = array();
        $response['rows'] = [];
        $response['labels'] = ['Remaining', 'Ideal', 'Forecast'];
        $response['keys'] = ['y1', 'y2'];

        $jql = $widgetConfig->getJqlQuery();

        $velocity = $widgetConfig->getVelocity();
        $calcBase = $widgetConfig->getCalcBase();

        $startDate = new \DateTime($widgetConfig->getStartDate());
        $endDate = new \DateTime($widgetConfig->getEndDate() . ' 23:59:59');

        $days = (int)$startDate->diff($endDate)->format('%a');
        $doneDays = (int)$startDate->diff(new \DateTime())->format('%a');
        $colors = ['#0b62a4', '#7A92A3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'];
        $storyPointField = 'customfield_10004';

        //$widget = $em->getRepository('MetricsBundle:Widgets')->find($widgetId);
        //$size = $widget->getSize();
        //var_dump($widget); exit;
        //$scaling = 1 / substr($size, -1);

        $issueService = new IssueService($this->get('JiraCoreService')->getLoginCredentials());

        $row = 1;
        $updateCounter = 0;
        $interval = '-1 day';
        $now = clone $endDate;
        $data = $this->_getDataArray($widgetId, 1);

        for ($now; $now > $startDate; $now->modify($interval))
        {
            $keyDate = new \DateTime($now->format('Y-m-d'));
            $dateTs = $keyDate->format('Y-m-d');

            if ($keyDate->getTimestamp()>time()) {
                continue;
            }

            // check for not persisted data in cache
            if ($now->format('Y-m-d') == date('Y-m-d')
                && $cacheValue = $cache->getValue('JiraBurnDownWidgetBundle_today', $widgetId, null, 60)) {
                $this->_addData($response['data'], $dateTs, 'y1', $cacheValue);
            } elseif (!isset($data[$dateTs]) && $updateCounter<1) {
                // get new data and persist
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
                            $entity->setValue($issues->getTotal());
                            break;

                        default:
                    }

                    // don't persists data which are not final
                    if ($now->format('Y-m-d') == date('Y-m-d')) {
                        $cache->setValue('JiraBurnDownWidgetBundle_today', $widgetId, $entity->getValue());
                    } else {
                        $em->persist($entity);
                        $em->flush();
                    }

                    $this->_addData($response['data'], $dateTs, 'y1', $entity->getValue());
                } catch (JiraException $e) {
                    $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                    return new Response(json_encode($response), Response::HTTP_OK);
                }
            } elseif (isset($data[$dateTs])) {
                $this->_addData($response['data'], $dateTs, 'y1', $data[$dateTs]);
            } else {
                $response['need-update'] = true;
            }
        }

        // add estimated end date
        $currentDate = new \DateTime();
        $restDays = 0;
        $performance = 0;
        $finishDate = $endDate->format('Y-m-d');
        if (!isset($response['need-update'])
            && isset($response['data'][$currentDate->format('Y-m-d')])
            && isset($response['data'][$currentDate->format('Y-m-d')]['y1'])) {

            $valueToday = $response['data'][$currentDate->format('Y-m-d')]['y1'];

            $response['data'][$currentDate->format('Y-m-d')]['y3'] = $valueToday;

            if ($velocity) {
                $performance = $velocity;
            } else {
                $performance = ($data[$startDate->format('Y-m-d')] - $valueToday) / $doneDays;
            }

            $restDays = ceil($valueToday / $performance);
            $finishDate = $currentDate->modify('+' . $restDays . ' days')->format('Y-m-d');

            if ($restDays - ($days - $doneDays) > 0) {
                $colors[2] = '#FF0000';
            }

            if (!isset($response['data'][$finishDate])) {
                $response['data'][$finishDate] = array();
            }

            $response['data'][$finishDate]['date'] = $finishDate;
            $response['data'][$finishDate]['y3'] = 0;
            $response['keys'][] = 'y3';
            $response['rest-days'] = $restDays;
            $response['diff-days'] = $restDays - ($days - $doneDays);
        }

        // generate burn down line
        if (!isset($response['need-update']) && isset($response['data'][$startDate->format('Y-m-d')]['y1'])) {
            $response['data'][$startDate->format('Y-m-d')]['y2'] = $response['data'][$startDate->format('Y-m-d')]['y1'];
            $response['data'][$endDate->format('Y-m-d')]['date'] = $endDate->format('Y-m-d');
            $response['data'][$endDate->format('Y-m-d')]['y2'] = 0;
        }

        // generate chart legend
        $response['legend'] = '';
        foreach ($response['labels'] as $key=>$label) {
            $response['legend'] .= '&nbsp;&nbsp;<i style="color:' . $colors[$key] . '" class="fa fa-circle"></i> ' . $label;
        }

        $response['colors'] = $colors;
        $response['data'] = array_values($response['data']);
        $response['total-days'] = $days;
        $response['done-days'] = $doneDays;
        $response['left-days'] = $days - $doneDays;
        $response['sprint-end'] = $endDate->format('Y-m-d');
        $response['estimated-end-date'] = $finishDate;
        $response['performance'] = $performance;

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
                $result[$entity->getDate()->format('Y-m-d')] = $entity->getValue();
            }
        }

        return $result;
    }

}
