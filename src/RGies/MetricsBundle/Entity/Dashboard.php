<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dashboard
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\DashboardRepository")
 */
class Dashboard
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="pos", type="integer")
     */
    private $pos=99;

    /**
     * @ORM\OneToMany(targetEntity="RGies\MetricsBundle\Entity\Widgets", mappedBy="dashboard")
     */
    private $widgets;

    /**
     * @ORM\OneToMany(targetEntity="RGies\MetricsBundle\Entity\Params", mappedBy="dashboard")
     */
    private $params;

    /**
     * @ORM\Column(type="integer", options={"default":"1"})
     */
    private $domain;

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

        $this->widget = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Dashboard
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set pos
     *
     * @param integer $pos
     *
     * @return Dashboard
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos
     *
     * @return integer
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Add widget
     *
     * @param \RGies\MetricsBundle\Entity\Widgets $widget
     *
     * @return Dashboard
     */
    public function addWidgets(\RGies\MetricsBundle\Entity\Widgets $widget)
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * Remove widget
     *
     * @param \RGies\MetricsBundle\Entity\Widgets $widget
     */
    public function removeWidget(\RGies\MetricsBundle\Entity\Widgets $widget)
    {
        $this->widgets->removeElement($widget);
    }

    /**
     * Get widget
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Dashboard
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add widget
     *
     * @param \RGies\MetricsBundle\Entity\Widgets $widget
     *
     * @return Dashboard
     */
    public function addWidget(\RGies\MetricsBundle\Entity\Widgets $widget)
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * Add param
     *
     * @param \RGies\MetricsBundle\Entity\Params $param
     *
     * @return Dashboard
     */
    public function addParam(\RGies\MetricsBundle\Entity\Params $param)
    {
        $this->params[] = $param;

        return $this;
    }

    /**
     * Remove param
     *
     * @param \RGies\MetricsBundle\Entity\Params $param
     */
    public function removeParam(\RGies\MetricsBundle\Entity\Params $param)
    {
        $this->params->removeElement($param);
    }

    /**
     * Get params
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set domain
     *
     * @param string $domain
     *
     * @return User
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
