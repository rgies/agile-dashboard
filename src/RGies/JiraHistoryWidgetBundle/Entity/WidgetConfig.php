<?php

namespace RGies\JiraHistoryWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="JiraHistoryWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\JiraHistoryWidgetBundle\Entity\WidgetConfigRepository")
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
     * @ORM\Column(name="chart_type", type="string", length=100, nullable=true)
     */
    private $chart_type='Area';

    /**
     * @var string
     *
     * @ORM\Column(name="data_source", type="string", length=100, nullable=true)
     */
    private $data_source='Count';

    /**
     * @var string
     *
     * @ORM\Column(name="start_date", type="string", length=100, nullable=true)
     */
    private $start_date;

    /**
     * @var string
     *
     * @ORM\Column(name="end_date", type="string", length=100, nullable=true)
     */
    private $end_date;

    /**
     * @var string
     *
     * @ORM\Column(name="label1", type="string", length=255, nullable=true)
     */
    private $label1;


    /**
     * @var string
     *
     * @ORM\Column(name="jql_query1", type="text", nullable=true)
     */
    private $jql_query1;


    /**
     * Constructor
     */
    public function __construct($init = null)
    {
        if ($init !== null)
        {
            foreach ((array)$init as $key=>$value)
            {
                $this->$key = $value;
            }
            $this->id = null;
        }
    }

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
     * Set startDate
     *
     * @param string $startDate
     *
     * @return WidgetConfig
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     *
     * @return WidgetConfig
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set label1
     *
     * @param string $label1
     *
     * @return WidgetConfig
     */
    public function setLabel1($label1)
    {
        $this->label1 = $label1;

        return $this;
    }

    /**
     * Get label1
     *
     * @return string
     */
    public function getLabel1()
    {
        return $this->label1;
    }

    /**
     * Set jqlQuery1
     *
     * @param string $jqlQuery1
     *
     * @return WidgetConfig
     */
    public function setJqlQuery1($jqlQuery1)
    {
        $this->jql_query1 = $jqlQuery1;

        return $this;
    }

    /**
     * Get jqlQuery1
     *
     * @return string
     */
    public function getJqlQuery1()
    {
        return $this->jql_query1;
    }

    /**
     * Set chartType
     *
     * @param string $chartType
     *
     * @return WidgetConfig
     */
    public function setChartType($chartType)
    {
        $this->chart_type = $chartType;

        return $this;
    }

    /**
     * Get chartType
     *
     * @return string
     */
    public function getChartType()
    {
        return $this->chart_type;
    }

    /**
     * Set dataSource
     *
     * @param string $dataSource
     *
     * @return WidgetConfig
     */
    public function setDataSource($dataSource)
    {
        $this->data_source = $dataSource;

        return $this;
    }

    /**
     * Get dataSource
     *
     * @return string
     */
    public function getDataSource()
    {
        return $this->data_source;
    }
}
