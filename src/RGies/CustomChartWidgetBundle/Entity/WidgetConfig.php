<?php

namespace RGies\CustomChartWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="CustomChartWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\CustomChartWidgetBundle\Entity\WidgetConfigRepository")
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
     * @ORM\Column(name="labels", type="string", length=255, nullable=true)
     */
    private $labels='Label1,Label2';

    /**
     * @var string
     *
     * @ORM\Column(name="dates", type="text")
     */
    private $dates='2017-01-01,2017-02-01,2017-03-01';

    /**
     * @var string
     *
     * @ORM\Column(name="datarows", type="text")
     */
    private $datarows="23,26,25\n45,50,51";


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
     * Set labels
     *
     * @param string $labels
     *
     * @return WidgetConfig
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Get labels
     *
     * @return string
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Set datarows
     *
     * @param string $datarows
     *
     * @return WidgetConfig
     */
    public function setDatarows($datarows)
    {
        $this->datarows = $datarows;

        return $this;
    }

    /**
     * Get datarows
     *
     * @return string
     */
    public function getDatarows()
    {
        return $this->datarows;
    }

    /**
     * Set dates
     *
     * @param string $dates
     *
     * @return WidgetConfig
     */
    public function setDates($dates)
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * Get dates
     *
     * @return string
     */
    public function getDates()
    {
        return $this->dates;
    }
}
