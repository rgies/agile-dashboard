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
     * @param $widgetId integer
     * @return object
     */
    public function getWidgetConfig($widgetId);

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @return string
     */
    public function getWidgetIncludePath();

    /**
     * Get action name to widget configuration.
     *
     * @return string
     */
    public function getWidgetEditActionName();

    /**
     * Deletes widget configuration.
     *
     * @param $widgetId integer
     */
    public function deleteWidgetConfig($widgetId);

}