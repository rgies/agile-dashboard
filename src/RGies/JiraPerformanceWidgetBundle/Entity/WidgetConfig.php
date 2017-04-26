<?php

namespace RGies\JiraPerformanceWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="JiraPerformanceWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\JiraPerformanceWidgetBundle\Entity\WidgetConfigRepository")
 */
class WidgetConfig
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="widget_id", type="integer")
     */
    private $widget_id;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=100)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="jql_query", type="text")
     */
    private $jql_query;

    /**
     * @var string
     *
     * @ORM\Column(name="extended_info", type="text", length=100, nullable=true)
     */
    private $extended_info = '';


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set widgetId
     *
     * @param integer $widgetId
     *
     * @return WidgetConfig
     */
    public function setWidgetId($widgetId)
    {
        $this->widget_id = $widgetId;

        return $this;
    }

    /**
     * Get widgetId
     *
     * @return integer
     */
    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * Set datarow1
     *
     * @param string $datarow1
     *
     * @return WidgetConfig
     */
    public function setDatarow1($datarow1)
    {
        $this->datarow1 = $datarow1;

        return $this;
    }

    /**
     * Get datarow1
     *
     * @return string
     */
    public function getDatarow1()
    {
        return $this->datarow1;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return WidgetConfig
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set jqlQuery
     *
     * @param string $jqlQuery
     *
     * @return WidgetConfig
     */
    public function setJqlQuery($jqlQuery)
    {
        $this->jql_query = $jqlQuery;

        return $this;
    }

    /**
     * Get jqlQuery
     *
     * @return string
     */
    public function getJqlQuery()
    {
        return $this->jql_query;
    }

    /**
     * Set extendedInfo
     *
     * @param string $extendedInfo
     *
     * @return WidgetConfig
     */
    public function setExtendedInfo($extendedInfo)
    {
        $this->extended_info = $extendedInfo;

        return $this;
    }

    /**
     * Get extendedInfo
     *
     * @return string
     */
    public function getExtendedInfo()
    {
        return $this->extended_info;
    }
}
