<?php

namespace RGies\SeparatorWidgetBundle\Controller;

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
 * @Route("/separator_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="SeparatorWidgetBundle-collect-data")
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
        $widgetConfig   = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);


        // ======================================================
        // INSERT HERE YOUR CODE TO COLLECT THE NEEDED DATA
        // ======================================================
        $response = array(
            'value' => mt_rand (10,100)
        );
        // ======================================================


        return new Response(json_encode($response), Response::HTTP_OK);
    }
}
