<?php

namespace RGies\JiraTimelineWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Project\ProjectService;
use JiraRestApi\JiraException;

/**
 * Widget controller.
 *
 * @Route("/jira_timeline_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraTimelineWidgetBundle-collect-data")
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
        $selectedDate   = null;
        $html           = null;
        $response       = array();
        $days           = null;

        // get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraTimelineWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        // get widget configuration
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);
        $projectList = explode(',', $widgetConfig->getProjectName());
        $credentials = $this->get('JiraCoreService')->getLoginCredentials();
        $data = array();

        foreach ($projectList as $projectName) {
            try {
                $project = new ProjectService($credentials);

                $items = $project->get($projectName);

                //print_r($items->versions); exit;
            } catch (JiraException $e) {
                $response['warning'] = wordwrap('Wrong start date format: ' . $e->getMessage(), 38, '<br/>');
                return new Response(json_encode($response), Response::HTTP_OK);
            }

            if ($items->versions) {
                foreach ($items->versions as $item) {
                    if (isset($item->releaseDate) && !$item->archived) {
                        $date = new \DateTime($item->releaseDate);
                        $item->link = $credentials->getjiraHost() . '/projects/' . $projectName;
                        $data[$date->getTimestamp()] = $item;
                    }
                }
            }
        }

        if (count($data) > 0) {

            ksort($data);

            foreach ($data as $key=>$item) {
                if (isset($item->releaseDate)) {
                    $selectedDate = new \DateTime($item->releaseDate);
                    if ($key > time()-(3600*24)) {
                        break;
                    }
                }
            }

            if ($selectedDate && $selectedDate->getTimestamp() > time()) {
                $today = new \DateTime();
                $dayDiff = $today->diff($selectedDate);
                $days = $dayDiff->days + 1;
                $response['days-to-milestone'] = $days;
            }

            $html = $this->renderView(
                'JiraTimelineWidgetBundle:Default:timeline.html.twig',
                array(
                    'id' => $widgetId,
                    'selectedDate' => $selectedDate,
                    'items' => $data,
                    'dayDiff' => $days
                )
            );

            $response['html'] = $html;
        }


        // Cache response data
        $cache->setValue('JiraTimelineWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
