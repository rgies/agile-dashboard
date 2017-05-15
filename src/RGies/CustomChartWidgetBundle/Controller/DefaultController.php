<?php

namespace RGies\CustomChartWidgetBundle\Controller;

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
 * @Route("/custom_chart_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="CustomChartWidgetBundle-collect-data")
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
        if ($cacheValue = $cache->getValue('CustomChartWidgetBundle', $widgetId, null, $updateInterval)) {
            //return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $response['data'] = array();
        $response['keys'] = array();

        $labels = explode(',', $widgetConfig->getLabels());
        $dates = explode(',', $widgetConfig->getDates());
        $rows = explode("\n", $widgetConfig->getDatarows());
        
        $z = 0;
        foreach ($rows as $row)
        {
            $response['keys'][] = 'y' . ($z + 1);
            $z++;
        }


        $data = array();
        $r = 0;
        foreach ($dates as $date)
        {
            $data = array('date' => $date);

            $z = 0;
            foreach ($rows as $row)
            {
                $items = explode(',', $row);
                $key = 'y' . ($z + 1);
                $data[$key] = $items[$r];
                $z++;
            }

            $response['data'][] = $data;
            $r++;
        }

        if ($widgetConfig->getChartType() == 'Donut') {
            $response['data'] = [];
            foreach ($labels as $key=>$label) {
                $response['data'][] = array('label' => $label, 'value' => 20);
            }
        }

        $response['labels'] = $labels;

        // Cache response data
        $cache->setValue('CustomChartWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
