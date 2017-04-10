<?php
/**
 * Widget plugin service.
 */

namespace RGies\JiraEstimatesWidgetBundle\Services;

use RGies\JiraEstimatesWidgetBundle\Entity\WidgetConfig;
use RGies\MetricsBundle\Interfaces\WidgetPluginInterface;

/**
 * Class WidgetService.
 *
 * @package RGies\JiraEstimatesWidgetBundle\Services
 */
class WidgetPluginService implements WidgetPluginInterface
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var array Plugin configuration
     */
    private $_config;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $config)
    {
        $this->_doctrine    = $doctrine;
        $this->_config      = $config;
    }

    /**
     * Gets widget configuration.
     *
     * @param integer $widgetId
     * @param string $widgetType Type name of the widget
     * @return object
     */
    public function getWidgetConfig($widgetId, $widgetType)
    {
        $em = $this->_doctrine->getManager();

        $query = $em->getRepository('JiraEstimatesWidgetBundle:WidgetConfig')->createQueryBuilder('i')
            ->where('i.widget_id = :id')
            ->setParameter('id', $widgetId);
        $items = $query->getQuery()->getResult();

        if ($items) {
            return $items[0];
        }

        return null;
    }

    /**
     * Deletes widget configuration.
     *
     * @param integer $widgetId
     * @param string $widgetType Type name of the widget
     */
    public function deleteWidgetConfig($widgetId, $widgetType)
    {
        $em = $this->_doctrine->getManager();

        if ($entity = $this->getWidgetConfig($widgetId, $widgetType)) {
            $em->remove($entity);
            $em->flush();
        }
    }

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetIncludePath($widgetType)
    {
        return $this->_config['widget_view'];
    }

    /**
     * Get action name to widget configuration.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetEditActionName($widgetType)
    {
        return $this->_config['edit_action'];
    }
}