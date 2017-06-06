<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User
 *
 * 
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(name="is_active", columns={"is_active"})})
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=50, nullable=false, name="username")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64, nullable=false, name="password")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=false, name="role")
     */
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true, name="jobtitle")
     */
    private $jobtitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=60, nullable=false, name="email")
     */
    private $email;

    /**
     * @ORM\ManyToMany(targetEntity="RGies\MetricsBundle\Entity\UserGroup", mappedBy="user")
     */
    private $usergroup;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, name="is_active")
     * 
     */
    private $is_active;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_login_date;


    /**
     * Entity constructor
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
        $this->last_login_date = null;
        $this->role = 'ROLE_USER';
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->modified = new \DateTime();
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        if ($password)
        {
            $this->modified = new \DateTime();
            $this->password = hash ('sha256', $password);
        }

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->modified = new \DateTime();
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->modified = new \DateTime();
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->modified = new \DateTime();
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->modified = new \DateTime();
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->modified = new \DateTime();
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Add user group
     *
     * @param \RGies\MetricsBundle\Entity\UserGroup $userGroup
     * @return User
     */
    public function addUserGroup(\RGies\MetricsBundle\Entity\UserGroup $userGroup)
    {
        $this->usergroup[] = $userGroup;

        return $this;
    }

    /**
     * Remove user group
     *
     * @param \RGies\MetricsBundle\Entity\UserGroup $userGroup
     */
    public function removeActivity(\RGies\MetricsBundle\Entity\UserGroup $userGroup)
    {
        $this->usergroup->removeElement($userGroup);
    }

    /**
     * Get user group
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserGroup()
    {
        return $this->usergroup;
    }

    // ===========================================
    // User Interface
    // ===========================================

    /**
     * Get role
     *
     * @return string
     */
    public function getRoles()
    {
        return array($this->role);
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    // ===========================================
    // Advanced Interface
    // ===========================================

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->is_active;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return User
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
     * @return User
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
     * Set jobtitle
     *
     * @param string $jobtitle
     * @return User
     */
    public function setJobtitle($jobtitle)
    {
        $this->jobtitle = $jobtitle;

        return $this;
    }

    /**
     * Get jobtitle
     *
     * @return string 
     */
    public function getJobtitle()
    {
        return $this->jobtitle;
    }

    /**
     * Set lastLoginDate
     *
     * @param \DateTime $lastLoginDate
     * @return User
     */
    public function setLastLoginDate($lastLoginDate)
    {
        $this->last_login_date = $lastLoginDate;

        return $this;
    }

    /**
     * Get lastLoginDate
     *
     * @return \DateTime 
     */
    public function getLastLoginDate()
    {
        return $this->last_login_date;
    }
}
