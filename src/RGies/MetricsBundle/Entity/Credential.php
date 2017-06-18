<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Credentials
 *
 * @ORM\Table(indexes={@ORM\Index(name="get_value", columns={"provider"})})
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\CredentialRepository")
 */
class Credential
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
     * @ORM\Column(name="provider", type="string", length=64)
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="updated", type="integer")
     */
    private $updated;

    /**
     * @var integer
     *
     * @ORM\Column(name="created", type="integer")
     */
    private $created;

    /**
     * @ORM\Column(type="integer", options={"default":"1"})
     */
    private $domain;

    /**
     * @var boolean
     *
     * @ORM\Column(name="connected", type="boolean", nullable=true, options={"default":"0"})
     */
    private $connected=false;


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
     * Set provider
     *
     * @param string $provider
     *
     * @return Credential
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Credential
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set updated
     *
     * @param integer $updated
     *
     * @return Credential
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return integer
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Credential
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
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

    /**
     * Set connected
     *
     * @param boolean $connected
     *
     * @return Credential
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;

        return $this;
    }

    /**
     * Get connected
     *
     * @return boolean
     */
    public function getConnected()
    {
        return $this->connected;
    }
}
