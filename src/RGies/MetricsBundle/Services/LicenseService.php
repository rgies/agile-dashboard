<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 14.06.17
 * Time: 22:22
 */

namespace RGies\MetricsBundle\Services;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class LicenseService.
 *
 * @package RGies\MetricsBundle\Services
 */
class LicenseService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var \SessionHandler
     */
    protected $_session;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $_serviceContainer;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $session, $serviceContainer)
    {
        $this->_doctrine = $doctrine;
        $this->_session = $session;
        $this->_serviceContainer = $serviceContainer;
    }

    /**
     * Checks if the defined element limit is reached.
     *
     * @param string $entityName Entity to check (Dashboard, User, Widgets, etc.)
     * @return boolean
     */
    public function limitReached($entityName)
    {
        $reached = false;
        $limitParameter = 'limit_' . mb_strtolower($entityName) . '_count';

        if ($this->_serviceContainer->hasParameter($limitParameter) && $this->_serviceContainer->getParameter($limitParameter)) {
            $em = $this->_doctrine->getManager();
            $domain = $this->_serviceContainer->get('session')->get('domain');

            $count = $em->getRepository('MetricsBundle:' . ucfirst($entityName))
                ->createQueryBuilder('e')
                ->select('count(e.id)')
                ->where('e.domain = :domain')
                ->setParameter('domain', $domain)
                ->getQuery()
                ->getSingleScalarResult();

            $reached = ($this->_serviceContainer->getParameter($limitParameter) > $count) ? false : true;
        }

        return $reached;
    }


}
