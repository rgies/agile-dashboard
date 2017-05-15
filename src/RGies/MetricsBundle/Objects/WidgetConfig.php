<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 15.05.17
 * Time: 23:11
 */

namespace RGies\MetricsBundle\Objects;

use RGies\MetricsBundle\Entity\Widgets;

/**
 * Class WidgetService.
 *
 * @package RGies\MetricsBundle\Objects
 */
class WidgetConfig
{
    private $_widgetConfig;
    private $_params;

    public function __construct($widgetId, $widgetConfig, $doctrine)
    {
        $this->_widgetConfig = $widgetConfig;

        $em = $doctrine->getManager();
        $widget = $em->getRepository('MetricsBundle:Widgets')->find($widgetId);
        $this->_params = $em->getRepository('MetricsBundle:Params')->findBy(
            array('dashboard' => $widget->getDashboard()->getId())
        );
    }

    public function __call($name, $args)
    {
        $value = $this->_widgetConfig->$name();

        foreach ($this->_params as $entity) {
            $placeholder = '%' . $entity->getPlaceholder() . '%';
            $value = str_replace($placeholder, $entity->getValue(), $value);
        }

        return $value;
    }


    public function __get($name)
    {
        $value = $this->_widgetConfig->$name;

        foreach ($this->_params as $entity) {
            $placeholder = '%' . $entity->getPlaceholder() . '%';
            $value = str_replace($placeholder, $entity->getValue(), $value);
        }

        return $value;
    }

    public function __set($name, $value)
    {
        if (substr($name,0,3) != 'set') {
            $name = 'set' . ucfirst($name);
        }

        $this->_widgetConfig->$name($value);
    }

    public function __clone()
    {
        return clone $this->_widgetConfig;
    }
}