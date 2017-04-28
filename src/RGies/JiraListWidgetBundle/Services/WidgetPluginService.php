<?php
/**
 * Widget plugin service.
 */

namespace RGies\JiraListWidgetBundle\Services;

use RGies\JiraListWidgetBundle\Entity\WidgetConfig;
use RGies\MetricsBundle\Interfaces\WidgetPluginInterface;

/**
 * Class WidgetService.
 *
 * @package RGies\JiraListWidgetBundle\Services
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
     * @param string $widgetType Type name of the widget
     * @param integer $widgetId OPTIONAL Id of the widget
     * @param boolean $toArray  OPTIONAL True for result type array
     * @return object | array | null
     */
    public function getWidgetConfig($widgetType, $widgetId = null, $toArray = false)
    {
        if ($widgetId === null) {
            return new WidgetConfig();
        }

        $em = $this->_doctrine->getManager();
        $query = $em->getRepository('JiraListWidgetBundle:WidgetConfig')->createQueryBuilder('i')
            ->where('i.widget_id = :id')
            ->setParameter('id', $widgetId);

        if ($toArray) {
            $items = $query->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        } else {
            $items = $query->getQuery()->getResult();
        }

        if ($items) {
            return $items[0];
        }

        return null;
    }

    /**
     * Set configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param array $data Config data
     */
    public function setWidgetConfig($widgetType, $data)
    {
        $em = $this->_doctrine->getManager();

        $widgetConfig = new WidgetConfig($data);

        $em->persist($widgetConfig);
        $em->flush();
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