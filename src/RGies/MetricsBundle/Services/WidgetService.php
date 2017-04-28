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
     * @param string $widgetType Type name of the widget
     * @param integer $widgetId OPTIONAL ID of the widget
     * @param boolean $toArray OPTIONAL True for result type array
     * @return object | array | null
     */
    public function getWidgetConfig($widgetType, $widgetId = null, $toArray = false)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        return $widgetService->getWidgetConfig($widgetType, $widgetId, $toArray);
    }

    /**
     * Set configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param array $data Config value
     */
    public function setWidgetConfig($widgetType, $data)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        $widgetService->setWidgetConfig($widgetType, (array)$data);
    }

    /**
     * Deletes configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param $widgetId
     */
    public function deleteWidgetConfig($widgetType, $widgetId)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        $widgetService->deleteWidgetConfig($widgetId, $widgetType);
    }

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetIncludePath($widgetType)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        return $widgetService->getWidgetIncludePath($widgetType);
    }

    /**
     * Loads the required service from given plugin type.
     *
     * @param string $widgetType Type name of the widget
     * @return object
     */
    protected function _loadPluginService($widgetType)
    {
        $list = explode('/', $widgetType, 2);
        $bundle = $list[0];
        $path = $this->_serviceContainer->get('kernel')->locateResource('@' . $bundle . '/Services');

        // load bundle plugin service
        require_once ($path . '/WidgetPluginService.php');
        $service = $this->_serviceContainer->get($bundle . 'Service');

        return $service;
    }


}