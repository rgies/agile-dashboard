<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Widgets
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\WidgetsRepository")
 */
class Widgets
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
     * @ORM\Column(name="type", type="string", length=100)
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled=true;

    /**
     * @ORM\ManyToOne(targetEntity="RGies\MetricsBundle\Entity\Dashboard", inversedBy="widgets")
     * @ORM\JoinColumn(name="dashboard_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $dashboard;

    /**
     * @var integer
     *
     * @ORM\Column(name="pos", type="integer", nullable=true)
     */
    private $pos;

    /**
     * @var integer
     *
     * @ORM\Column(name="update_interval", type="integer", nullable=true)
     */
    private $update_interval=600;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=100)
     */
    private $size;

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
     * @return Widgets
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
     * Set type
     *
     * @param string $type
     *
     * @return Widgets
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Widgets
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set dashboard
     *
     * @param \RGies\MetricsBundle\Entity\Dashboard $dashboard
     *
     * @return Widgets
     */
    public function setDashboard(\RGies\MetricsBundle\Entity\Dashboard $dashboard = null)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * Get dashboard
     *
     * @return \RGies\MetricsBundle\Entity\Dashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * Set pos
     *
     * @param integer $pos
     *
     * @return Widgets
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
     * Set updateInterval
     *
     * @param integer $updateInterval
     *
     * @return Widgets
     */
    public function setUpdateInterval($updateInterval)
    {
        $this->update_interval = $updateInterval;

        return $this;
    }

    /**
     * Get updateInterval
     *
     * @return integer
     */
    public function getUpdateInterval()
    {
        return $this->update_interval;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return Widgets
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
