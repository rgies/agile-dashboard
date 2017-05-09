<?php

namespace RGies\CustomTimelineWidgetBundle\Controller;

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
 * @Route("/custom_timeline_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="CustomTimelineWidgetBundle-collect-data")
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
        $selectedDate   = null;
        $html           = null;
        $response       = array();
        $days           = null;

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('CustomTimelineWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        //$widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('CustomTimelineWidgetBundle:WidgetConfig')->createQueryBuilder('i')
            ->where('i.widget_id = :id')
            ->orderBy('i.date')
            ->setParameter('id', $widgetId);
        $items = $query->getQuery()->getResult();

        if ($items) {
            foreach ($items as $item) {
                $selectedDate = $item->getDate();
                if ($item->getDate()->getTimestamp()>time()-(3600*24)) {
                    break;
                }
            }

            if ($selectedDate && $selectedDate->getTimestamp()>time()) {
                $today = new \DateTime();
                $dayDiff = $today->diff($selectedDate);
                $days = $dayDiff->days + 1;
                $response['days-to-milestone'] = $days;
            }

            $html = $this->renderView(
                'CustomTimelineWidgetBundle:Default:timeline.html.twig',
                array(
                    'id' => $widgetId,
                    'selectedDate' => $selectedDate,
                    'items' => $items,
                    'dayDiff' => $days
                )
            );

            $response['html'] = $html;
        }

        // Cache response data
        $cache->setValue('CustomTimelineWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
