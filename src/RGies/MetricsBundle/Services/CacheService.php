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

    /**
     * Class constructor.
     */
    public function __construct($doctrine)
    {
        $this->_doctrine = $doctrine;
    }

    /**
     * Set cache value.
     *
     * @param string $entity Unique name of entity
     * @param integer $id    Data set id
     * @param string $value  Value to store in cache
     */
    public function setValue($entity, $id, $value)
    {
        $em = $this->_doctrine->getManager();

        $cache = new Cache();
        $cache->setEntity($entity)
            ->setUid($id)
            ->setValue($value)
            ->setCreated(time());

        $em->persist($cache);
        $em->flush();

        if (mt_rand(1,10) == 5) {
            $this->garbageCollection();
        }
    }

    /**
     * Get cache value.
     *
     * @param string $entity    Unique name of entity
     * @param integer $id       Data set id
     * @param string $default   OPTIONAL Default value
     * @param integer $lifetime OPTIONAL Lifetime of the requested value
     * @return string|null      Null or stored value
     */
    public function getValue($entity, $id, $default=null, $lifetime=600)
    {
        $em = $this->_doctrine->getManager();
        $cache = $em->getRepository('MetricsBundle:Cache')
            ->createQueryBuilder('e')
            ->where('e.entity = :entity')
            ->andWhere('e.uid = :id')
            ->orderBy('e.created', 'DESC')
            ->setParameter('entity', $entity)
            ->setParameter('id', $id)
            ->getQuery()->getResult();

        if ($cache && $cache[0]->getCreated()+$lifetime > time())
        {
            return $cache[0]->getValue();
        }

        return $default;
    }

    public function getValueItem($entity, $id, $item, $default=null, $lifetime=600)
    {
        $items = json_decode($this->getValue($entity, $id, '[]', $lifetime), true);

        if (isset($items[$item])) {
            return $items[$item];
        }

        return $default;
    }

    /**
     * Delete cache value.
     *
     * @param string $entity Unique name of entity
     * @param integer $id    OPTIONAL Data set id
     * @return integer       Number of deleted items
     */
    public function deleteValue($entity, $id = null)
    {
        $em = $this->_doctrine->getManager();

        $query = 'delete from MetricsBundle:Cache st where st.entity = :entity';

        if ($id) {
            $query = $query . ' and st.uid = :id';
        }

        $q = $em->createQuery($query);
        $q->setParameter('entity', $entity);

        if ($id) {
            $q->setParameter('id', $id);
        }

        return $q->execute();
    }

    /**
     * Removes all cache items with end of lifetime.
     */
    public function garbageCollection()
    {
        $em = $this->_doctrine->getManager();

        // Set max lifetime to 8h
        $lifetime = 8 * 3600;

        $q = $em->createQuery('delete from MetricsBundle:Cache st where st.created < :now');
        $q->setParameter('now', time() - $lifetime);
        return $q->execute();
    }

}