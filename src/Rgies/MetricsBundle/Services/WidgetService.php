<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 04.05.17
 * Time: 16:11
 */

namespace Rgies\MetricsBundle\Services;

use Rgies\MetricsBundle\Entity\Widgets;

/**
 * Class WidgetService.
 *
 * @package Rgies\MetricsBundle\Services
 */
class WidgetService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var array Array of registered widget types
     */
    private $_widgetTypes;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $widgetTypes)
    {
        $this->_doctrine = $doctrine;
        $this->_widgetTypes = $widgetTypes;
    }

    /**
     * Get configuration by given widget id.
     *
     * @param $widgetId
     */
    public function getWidgetConfig($widgetId)
    {

    }

}