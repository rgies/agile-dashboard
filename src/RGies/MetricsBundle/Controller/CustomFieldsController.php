<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Custom fields controller.
 *
 * @Route("/custom-fields")
 */
class CustomFieldsController extends Controller
{

    /**
     * Lists all Params entities.
     *
     * @Route("/input/{id}/{name}/{value}{placeholder}", name="default-input-field")
     * @Method("GET")
     * @Template()
     */
    public function inputFieldAction(Request $request, $id, $name, $value, $placeholder = '')
    {
        return array(
            'id'            => $id,
            'name'          => $name,
            'value'         => $value,
            'placeholder'   => $placeholder,
        );
    }


}
