<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JiraCountConfig
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\JiraCountConfigRepository")
 */
class JiraCountConfig
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
    private $widgetId;

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
     * @ORM\Column(name="history", type="string", length=100, nullable=true)
     */
    private $history;

    /**
     * @var string
     *
     * @ORM\Column(name="happy_expression", type="string", length=100, nullable=true)
     */
    private $happy_expression;

    /**
     * @var string
     *
     * @ORM\Column(name="unhappy_expression", type="string", length=100, nullable=true)
     */
    private $unhappy_expression;


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
     * Set widget id
     *
     * @param string $widgetId
     *
     * @return JiraCountConfig
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;

        return $this;
    }

    /**
     * Get widget id
     *
     * @return integer
     */
    public function getWidgetId()
    {
        return $this->widgetId;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return JiraCountConfig
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
     * @return JiraCountConfig
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
     * Set history
     *
     * @param string $history
     *
     * @return JiraCountConfig
     */
    public function setHistory($history)
    {
        $this->history = $history;

        return $this;
    }

    /**
     * Get history
     *
     * @return string
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Set happyExpression
     *
     * @param string $happyExpression
     *
     * @return JiraCountConfig
     */
    public function setHappyExpression($happyExpression)
    {
        $this->happy_expression = $happyExpression;

        return $this;
    }

    /**
     * Get happyExpression
     *
     * @return string
     */
    public function getHappyExpression()
    {
        return $this->happy_expression;
    }

    /**
     * Set unhappyExpression
     *
     * @param string $unhappyExpression
     *
     * @return JiraCountConfig
     */
    public function setUnhappyExpression($unhappyExpression)
    {
        $this->unhappy_expression = $unhappyExpression;

        return $this;
    }

    /**
     * Get unhappyExpression
     *
     * @return string
     */
    public function getUnhappyExpression()
    {
        return $this->unhappy_expression;
    }
}

