<?php

namespace RGies\JiraBurnDownWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Widget Data
 *
 * @ORM\Table(name="JiraBurnDownWidgetData")
 * @ORM\Entity(repositoryClass="RGies\JiraBurnDownWidgetBundle\Entity\WidgetDataRepository")
 */
class WidgetData
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
     * @var integer
     *
     * @ORM\Column(name="data_row", type="integer")
     */
    private $data_row;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;

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
     * @return WidgetData
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return WidgetData
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return WidgetData
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set dataRow
     *
     * @param integer $dataRow
     *
     * @return WidgetData
     */
    public function setDataRow($dataRow)
    {
        $this->data_row = $dataRow;

        return $this;
    }

    /**
     * Get dataRow
     *
     * @return integer
     */
    public function getDataRow()
    {
        return $this->data_row;
    }
}
