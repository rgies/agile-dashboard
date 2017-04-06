<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 04.05.17
 * Time: 16:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Widgets;

/**
 * Class WidgetService.
 *
 * @package RGies\MetricsBundle\Services
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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $_serviceContainer;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $widgetTypes, $serviceContainer)
    {
        $this->_doctrine            = $doctrine;
        $this->_widgetTypes         = $widgetTypes;
        $this->_serviceContainer    = $serviceContainer;
    }

    /**
     * Get action name to widget configuration.
     *
     * @param $widgetType string Widget type (bundle name)
     * @return string
     */
    public function getWidgetEditActionName($widgetType)
    {
        $widgetService = $this->_loadPluginService($widgetType);

        return $widgetService->getWidgetEditActionName($widgetType);
    }

    /**
     * Get configuration by given widget id.
     *
     * @param $widgetId
     */
    public function getWidgetConfig($widgetId)
    {

    }

    /**
     * Loads the required service from given plugin type.
     *
     * @param string $bundle Type name of plugin
     * @return object
     */
    protected function _loadPluginService($bundle)
    {
        $path = $this->_serviceContainer->get('kernel')->locateResource('@' . $bundle . '/Services');

        // load bundle plugin service
        require_once ($path . '/WidgetPluginService.php');
        $service = $this->_serviceContainer->get($bundle . 'Service');

        return $service;
    }


}