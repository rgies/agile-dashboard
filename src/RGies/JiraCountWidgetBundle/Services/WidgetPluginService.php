<?php
/**
 * Widget plugin service.
 */

namespace RGies\JiraCountWidgetBundle\Services;

use RGies\JiraCountWidgetBundle\Entity\WidgetConfig;
use RGies\MetricsBundle\Interfaces\WidgetPluginInterface;

/**
 * Class WidgetService.
 *
 * @package RGies\JiraCountWidgetBundle\Services
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
     * @param $widgetId integer
     * @return object
     */
    public function getWidgetConfig($widgetId)
    {
        $em = $this->_doctrine->getManager();

        $query = $em->getRepository('JiraCountWidgetBundle:WidgetConfig')->createQueryBuilder('i')
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
     * @param $widgetId integer
     */
    public function deleteWidgetConfig($widgetId)
    {
        $em = $this->_doctrine->getManager();

        if ($entity = $this->getWidgetConfig($widgetId)) {
            $em->remove($entity);
            $em->flush();
        }
    }

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @return string
     */
    public function getWidgetIncludePath()
    {
        return $this->_config['widget_view'];
    }

    /**
     * Get action name to widget configuration.
     *
     * @return string
     */
    public function getWidgetEditActionName()
    {
        return $this->_config['edit_action'];
    }
}