<?php

namespace RGies\JiraHistoryWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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


        $response = array();
        $response['data1'] = array(
            array('date'=>2017, 'value'=>157),
            array('date'=>'2017', 'value'=>163),
            array('date'=>'2017', 'value'=>177),
            array('date'=>'2017', 'value'=>152),
        );

        // Cache response data
        $cache->setValue('JiraHistoryWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
