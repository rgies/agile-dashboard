<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 10.05.17
 * Time: 8:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Cache;

/**
 * Class CredentialService.
 *
 * @package RGies\MetricsBundle\Services
 */
class CredentialService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    private $_config;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $config)
    {
        $this->_doctrine = $doctrine;
        $this->_config = $config;
    }

    /**
     * Set cache value.
     *
     * @param string $domain Component (Jira, Google, etc.)
     * @param array $data   Credential array (e.g. login, password)
     */
    public function saveCredential($domain, array $data)
    {
        $em = $this->_doctrine->getManager();

        $storageableDate = serialize($data);

        /*
        $cache = new Cache();
        $cache  -> setDomain($domain)
                -> setUid($id)
                -> setValue($value)
                -> setCreated(time());

        $em->persist($cache);
        $em->flush();
        */
    }

}