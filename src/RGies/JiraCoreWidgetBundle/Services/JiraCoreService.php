<?php
/**
 * Jirorea C service.
 */

namespace RGies\JiraCoreWidgetBundle\Services;

use RGies\JiraCoreWidgetBundle\Entity\Config;

/**
 * Class WidgetService.
 *
 * @package RGies\JiraCoreWidgetBundle\Services
 */
class JiraCoreService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var array Plugin configuration
     */
    private $_config;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $config)
    {
        $this->_doctrine    = $doctrine;
        $this->_config      = $config;
    }

    public function getLoginCredentials()
    {

    }

}