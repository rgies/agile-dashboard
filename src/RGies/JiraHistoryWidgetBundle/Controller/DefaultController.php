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

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraHistoryWidgetBundle', $widgetId, null, $updateInterval)) {
            //return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);
        $em = $this->getDoctrine()->getManager();
        $dataRepository = $em->getRepository('JiraHistoryWidgetBundle:WidgetData');

        $response = array();
        $response['data1'] = array();
        $startDate = new \DateTime();
        $endDate = new \DateTime();

        $label1 = $widgetConfig->getLabel1();
        $jql1 = $widgetConfig->getJqlQuery1();

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
        $days = $startDate->diff($endDate)->format('%a');
        $data = $this->_getDataArray($widgetId);

        $now = new \DateTime();
        $interval = '-1 day';

        // auto calculate interval
        if ($days > 200) {
            $interval = '-4 month';
            $now = new \DateTime('first day of this month');
        } elseif ($days > 60) {
            $interval = '-1 month';
            $now = new \DateTime('first day of this month');
        } elseif ($days > 10) {
            $interval = '-1 week';
            $now = new \DateTime('friday last week');
        }

        $issueService = new IssueService($this->_getLoginCredentials());


        for ($date = $now; $date > $startDate; $date->modify($interval))
        {
            $keyDate = new \DateTime($date->format('Y-m-d 12:00:00'));

            if (!isset($data[$keyDate->getTimestamp()])) {
                $updateCounter++;
                $jqlQuery = str_replace('%date%', $date->format('Y-m-d'), $jql1);

                try {
                    $issues = $issueService->search($jqlQuery, 0, 10000, ['key','created','updated']);

                    $entity = new WidgetData();
                    $entity->setWidgetId($widgetId);
                    $entity->setDate($keyDate);
                    $entity->setValue($issues->getTotal());
                    $em->persist($entity);
                    $em->flush();

                    $response['data1'][] = array('date' => $keyDate->format('Y-m-d'), 'value' => $entity->getValue());


                } catch (JiraException $e) {
                    $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
                    return new Response(json_encode($response), Response::HTTP_OK);
                }
            } else {
                $response['data1'][] = array('date' => $keyDate->format('Y-m-d'), 'value' => $data[$keyDate->getTimestamp()]);
            }

            if ($updateCounter>1) {
                break;
            }
        }

        $response['days'] = $days;
        $response['label1'] = $label1;

        // Cache response data
        $cache->setValue('JiraHistoryWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _getDataArray($widgetId)
    {
        $result = array();

        $em = $this->getDoctrine()->getManager();
        $dataRepository = $em->getRepository('JiraHistoryWidgetBundle:WidgetData');

        $data = $dataRepository
            ->createQueryBuilder('d')
            ->where('d.widget_id = :id')
            //->orderBy('d.date', 'ASC')
            ->setParameter('id', $widgetId)
            ->getQuery()->getResult();

        if ($data) {
            foreach ($data as $entity) {
                $result[$entity->getDate()->getTimestamp()] = $entity->getValue();
            }
        }

        return $result;
    }

    /**
     * @return ArrayConfiguration
     */
    protected function _getLoginCredentials()
    {
        return new ArrayConfiguration(
            array(
                'jiraHost' => $this->getParameter('jira_host'),
                'jiraUser' => $this->getParameter('jira_user'),
                'jiraPassword' => $this->getParameter('jira_password'),
            )
        );
    }

}
