<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Domain
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\DomainRepository")
 */
class Domain
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $is_active;

    /**
     * @var integer
     *
     * @ORM\Column(name="dashboard_limit", type="integer", nullable=true)
     */
    private $dashboard_limit;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_limit", type="integer", nullable=true)
     */
    private $user_limit;

    /**
     * @var integer
     *
     * @ORM\Column(name="widget_limit", type="integer", nullable=true)
     */
    private $widget_limit;

    /**
     * Entity constructor
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
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
     * @return Domain
     */
    public function setTitle($title)
    {
        $this->modified = new \DateTime();
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
     * Set description
     *
     * @param string $description
     *
     * @return Domain
     */
    public function setDescription($description)
    {
        $this->modified = new \DateTime();
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Domain
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Domain
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Domain
     */
    public function setIsActive($isActive)
    {
        $this->modified = new \DateTime();
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set dashboardLimit
     *
     * @param integer $dashboardLimit
     *
     * @return Domain
     */
    public function setDashboardLimit($dashboardLimit)
    {
        $this->dashboard_limit = $dashboardLimit;

        return $this;
    }

    /**
     * Get dashboardLimit
     *
     * @return integer
     */
    public function getDashboardLimit()
    {
        return $this->dashboard_limit;
    }

    /**
     * Set userLimit
     *
     * @param integer $userLimit
     *
     * @return Domain
     */
    public function setUserLimit($userLimit)
    {
        $this->user_limit = $userLimit;

        return $this;
    }

    /**
     * Get userLimit
     *
     * @return integer
     */
    public function getUserLimit()
    {
        return $this->user_limit;
    }

    /**
     * Set widgetLimit
     *
     * @param integer $widgetLimit
     *
     * @return Domain
     */
    public function setWidgetLimit($widgetLimit)
    {
        $this->widget_limit = $widgetLimit;

        return $this;
    }

    /**
     * Get widgetLimit
     *
     * @return integer
     */
    public function getWidgetLimit()
    {
        return $this->widget_limit;
    }
}
