<?php

/**
 * Widget plugin interface.
 */
namespace RGies\MetricsBundle\Interfaces;

interface WidgetPluginInterface
{
    /**
     * Gets widget configuration.
     *
     * @param string $widgetType Type name of the widget
     * @param integer $widgetId OPTIONAL Id of the widget
     * @param boolean $toArray  OPTIONAL True for result type array
     * @return object | array | null
     */
    public function getWidgetConfig($widgetType, $widgetId = null, $toArray = false);

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetIncludePath($widgetType);

    /**
     * Get action name to widget configuration.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetEditActionName($widgetType);

    /**
     * Deletes widget configuration.
     *
     * @param integer $widgetId
     * @param string $widgetType Type name of the widget
     */
    public function deleteWidgetConfig($widgetId, $widgetType);

}