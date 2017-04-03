<?php

namespace Rgies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Widgets
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Rgies\MetricsBundle\Entity\WidgetsRepository")
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
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="Rgies\MetricsBundle\Entity\Dashboard", inversedBy="widgets")
     * @ORM\JoinColumn(name="dashboard_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $dashboard;

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
     * @param \Rgies\MetricsBundle\Entity\Dashboard $dashboard
     *
     * @return Widgets
     */
    public function setDashboard(\Rgies\MetricsBundle\Entity\Dashboard $dashboard = null)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * Get dashboard
     *
     * @return \Rgies\MetricsBundle\Entity\Dashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }
}
