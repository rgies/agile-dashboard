<?php

namespace RGies\MetricsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Recipe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RGies\MetricsBundle\Entity\RecipeRepository")
 */
class Recipe
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
     * @ORM\OneToMany(targetEntity="RGies\MetricsBundle\Entity\RecipeFields", mappedBy="recipe")
     */
    private $recipe_fields;


    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/png" })
     */
    private $image_url;

    /**
     * @var float
     *
     * @ORM\Column(name="rating", type="float")
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_type", type="string", length=100)
     */
    private $entity_type;

    /**
     * @var string
     *
     * @ORM\Column(name="bundle_name", type="string", length=100, nullable=true)
     */
    private $bundle_name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled=true;

    /**
     * @var string
     *
     * @ORM\Column(name="json_config", type="string", length=255, nullable=true)
     *
     * @Assert\File(mimeTypes={ "text/plain" })
     */
    private $json_config;


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
     * @return Recipe
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
     * Set description
     *
     * @param string $description
     *
     * @return Recipe
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
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return Recipe
     */
    public function setImageUrl($imageUrl)
    {
        $this->image_url = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * Set rating
     *
     * @param float $rating
     *
     * @return Recipe
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set widgetType
     *
     * @param string $widgetType
     *
     * @return Recipe
     */
    public function setWidgetType($widgetType)
    {
        $this->widget_type = $widgetType;

        return $this;
    }

    /**
     * Get widgetType
     *
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widget_type;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Recipe
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
     * Constructor
     */
    public function __construct()
    {
        $this->recipe_fields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set entityType
     *
     * @param string $entityType
     *
     * @return Recipe
     */
    public function setEntityType($entityType)
    {
        $this->entity_type = $entityType;

        return $this;
    }

    /**
     * Get entityType
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entity_type;
    }

    /**
     * Set bundleName
     *
     * @param string $bundleName
     *
     * @return Recipe
     */
    public function setBundleName($bundleName)
    {
        $this->bundle_name = $bundleName;

        return $this;
    }

    /**
     * Get bundleName
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundle_name;
    }

    /**
     * Set jsonConfig
     *
     * @param string $jsonConfig
     *
     * @return Recipe
     */
    public function setJsonConfig($jsonConfig)
    {
        $this->json_config = $jsonConfig;

        return $this;
    }

    /**
     * Get jsonConfig
     *
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->json_config;
    }

    /**
     * Add recipeField
     *
     * @param \RGies\MetricsBundle\Entity\RecipeFields $recipeField
     *
     * @return Recipe
     */
    public function addRecipeField(\RGies\MetricsBundle\Entity\RecipeFields $recipeField)
    {
        $this->recipe_fields[] = $recipeField;

        return $this;
    }

    /**
     * Remove recipeField
     *
     * @param \RGies\MetricsBundle\Entity\RecipeFields $recipeField
     */
    public function removeRecipeField(\RGies\MetricsBundle\Entity\RecipeFields $recipeField)
    {
        $this->recipe_fields->removeElement($recipeField);
    }

    /**
     * Get recipeFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecipeFields()
    {
        return $this->recipe_fields;
    }
}
