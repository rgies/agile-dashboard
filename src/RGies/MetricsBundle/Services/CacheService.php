<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 04.05.17
 * Time: 16:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Cache;

/**
 * Class WidgetService.
 *
 * @package RGies\MetricsBundle\Services
 */
class CacheService
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
     * @param string $domain Domain
     * @param string $id     Unique data id
     * @param string $value  Value to store in cache
     */
    public function setValue($domain, $id, $value)
    {
        $em = $this->_doctrine->getManager();

        $cache = new Cache();
        $cache  -> setDomain($domain)
                -> setUid($id)
                -> setValue($value)
                -> setCreated(time());

        $em->persist($cache);
        $em->flush();

        if (mt_rand(1,10) == 5) {
            $this->garbageCollection();
        }
    }

    /**
     * Get cache value.
     *
     * @param string $domain    Domain
     * @param string $id        Unique data id
     * @param string $default   OPTIONAL Default value
     * @param integer $lifetime OPTIONAL Lifetime of the requested value
     * @return string|null      Null or stored value
     */
    public function getValue($domain, $id, $default=null, $lifetime=null)
    {
        if ($lifetime === null) {
            $lifetime = $this->_config['default_lifetime'] * 60;
        }

        $em = $this->_doctrine->getManager();
        $cache = $em->getRepository('MetricsBundle:Cache')
            -> createQueryBuilder('e')
            -> where('e.domain = :domain')
            -> andWhere('e.uid = :id')
            -> orderBy('e.created', 'DESC')
            -> setParameter('domain', $domain)
            -> setParameter('id', $id)
            -> getQuery()->getResult();

        if ($cache && $cache[0]->getCreated()+$lifetime > time())
        {
            return $cache[0]->getValue();
        }

        return $default;
    }

    /**
     * Gets a single item from a json encoded cache entry.
     *
     * @param $domain
     * @param $id
     * @param $item
     * @param null $default
     * @param int $lifetime
     * @return string
     */
    public function getValueItem($domain, $id, $item, $default=null, $lifetime=600)
    {
        $items = json_decode($this->getValue($domain, $id, '[]', $lifetime), true);

        if (isset($items[$item])) {
            return $items[$item];
        }

        return $default;
    }

    /**
     * Delete cache value.
     *
     * @param string $domain Domain
     * @param string $id     Unique data id
     * @return integer       Number of deleted items
     */
    public function deleteValue($domain, $id = null)
    {
        $em = $this->_doctrine->getManager();

        $query = 'delete from MetricsBundle:Cache st where st.domain = :domain';

        if ($id) {
            $query = $query . ' and st.uid = :id';
        }

        $q = $em->createQuery($query);
        $q->setParameter('domain', $domain);

        if ($id) {
            $q->setParameter('id', $id);
        }

        return $q->execute();
    }

    /**
     * Deletes cache for all widgets at a specific dashboard.
     *
     * @param $dashboardId
     * @return integer Number of deleted items
     */
    public function deleteCacheByDashboardId($dashboardId)
    {
        $em = $this->_doctrine->getManager();

        $widgets = $em->getRepository('MetricsBundle:Widgets')->findBy(
            array('dashboard' => $dashboardId)
        );

        $widgetIdList = array();
        foreach ($widgets as $widget) {
            $widgetIdList[] = $widget->getId();
        }

        $query = 'delete from MetricsBundle:Cache st where st.uid IN ('
            . implode($widgetIdList, ',') . ')';

        $q = $em->createQuery($query);
        return $q->execute();
    }

    /**
     * Removes all cache items with end of lifetime.
     */
    public function garbageCollection()
    {
        $em = $this->_doctrine->getManager();

        // Set max lifetime
        $lifetime = $this->_config['max_lifetime'] * 60;

        $q = $em->createQuery('delete from MetricsBundle:Cache st where st.created < :now');
        $q->setParameter('now', time() - $lifetime);
        return $q->execute();
    }

}