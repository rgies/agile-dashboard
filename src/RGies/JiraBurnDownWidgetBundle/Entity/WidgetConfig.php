<?php

namespace RGies\JiraBurnDownWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="JiraBurnDownWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\JiraBurnDownWidgetBundle\Entity\WidgetConfigRepository")
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
     * @ORM\Column(name="calc_base", type="string", length=100)
     */
    private $calc_base;

    /**
     * @var string
     *
     * @ORM\Column(name="start_date", type="string", length=100)
     */
    private $start_date;

    /**
     * @var string
     *
     * @ORM\Column(name="end_date", type="string", length=100)
     */
    private $end_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="velocity", type="string", nullable=true)
     */
    private $velocity;

    /**
     * @var string
     *
     * @ORM\Column(name="jql_query", type="text")
     */
    private $jql_query;


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
     * Set calcBase
     *
     * @param string $calcBase
     *
     * @return WidgetConfig
     */
    public function setCalcBase($calcBase)
    {
        $this->calc_base = $calcBase;

        return $this;
    }

    /**
     * Get calcBase
     *
     * @return string
     */
    public function getCalcBase()
    {
        return $this->calc_base;
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
     * Set velocity
     *
     * @param integer $velocity
     *
     * @return WidgetConfig
     */
    public function setVelocity($velocity)
    {
        $this->velocity = $velocity;

        return $this;
    }

    /**
     * Get velocity
     *
     * @return integer
     */
    public function getVelocity()
    {
        return $this->velocity;
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
}
