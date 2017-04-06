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
     * @var integer
     *
     * @ORM\Column(name="pos", type="integer")
     */
    private $pos;

    /**
     * @ORM\OneToMany(targetEntity="RGies\MetricsBundle\Entity\Widgets", mappedBy="dashboard")
     */
    private $widgets;


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
     * Constructor
     */
    public function __construct()
    {
        $this->widget = new \Doctrine\Common\Collections\ArrayCollection();
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
}
