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
     * @return bool
     * @throws \Exception
     */
    public function limitReached($entityName)
    {
        $limit = null;
        $reached = false;

        $configParameter = 'limit_' . mb_strtolower($entityName) . '_count';
        $domainAttribute = 'get' . ucfirst($entityName) . 'Limit';

        $em = $this->_doctrine->getManager();

        $domain = $this->_serviceContainer->get('session')->get('domain');
        $domainEntity = $em->getRepository('MetricsBundle:Domain')->find($domain);

        if (! $domainEntity) {
            throw new \Exception('Fatal error: No domain defined');
        }

        if (method_exists($domainEntity, $domainAttribute) && $domainEntity->$domainAttribute()) {
            // limit set by domain entity
            $limit = $domainEntity->$domainAttribute();
        } elseif ($this->_serviceContainer->hasParameter($configParameter) && $this->_serviceContainer->getParameter($configParameter)) {
            // limit set by config
            $limit = $this->_serviceContainer->getParameter($configParameter);
        }

        if ($limit) {
            $count = $em->getRepository('MetricsBundle:' . ucfirst($entityName))
                ->createQueryBuilder('e')
                ->select('count(e.id)')
                ->where('e.domain = :domain')
                ->setParameter('domain', $domain)
                ->getQuery()
                ->getSingleScalarResult();

            $reached = ($limit > $count) ? false : true;
        }

        return $reached;
    }


}
