<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 28.03.15
 * Time: 08:12
 */

namespace RGies\MetricsBundle\Services;

use Symfony\Component\Validator\Constraints\DateTime;
use RGies\MetricsBundle\Entity\SecurityToken;

/**
 * Class SecurityTokenService.
 *
 * @package RGies\MetricsBundle\Services
 */
class SecurityTokenService
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
     * Creates new token.
     *
     * @param integer $timeFrame Valid time frame in hours
     * @param string $context Target context e.g. user_registration
     * @param array $params Array of params to store at the token
     * @return string   Token value
     */
    public function create($timeFrame, $context, array $params=array())
    {
        $em = $this->_doctrine->getManager();

        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('PT' . $timeFrame . 'H'));

        $token = new SecurityToken();
        $token->setToken(md5(uniqid('AWS', true)));
        $token->setDueDate($dateTime);
        $token->setContext($context);
        $token->setParams(serialize($params));

        $em->persist($token);
        $em->flush();

        $this->garbageCollection();

        return $token->getToken();
    }

    /**
     * Check if token is valid.
     *
     * @param string $token
     * @param string $context
     * @return bool|array
     */
    public function isValid($token, $context)
    {
        $em = $this->_doctrine->getManager();

        $this->garbageCollection();

        $st = $em->getRepository('MetricsBundle:SecurityToken');

        $query = $st->createQueryBuilder('s')
            ->where('s.token = :token')
            ->andWhere('s.context = :context')
            ->setParameter('token', $token)
            ->setParameter('context', $context);

        $result = $query->getQuery()->getResult();

        if (count($result))
        {
            $entity = $result[0];
            $params = unserialize($entity->getParams());

            return $params;
        }

        return false;
    }

    /**
     * Validates token
     *
     * @param string $token
     * @param string $context
     * @return array|null   If valid it returns the stored params
     */
    public function validate($token, $context)
    {
        $em = $this->_doctrine->getManager();

        $this->garbageCollection();

        $st = $em->getRepository('MetricsBundle:SecurityToken');

        $query = $st->createQueryBuilder('s')
            ->where('s.token = :token')
            ->andWhere('s.context = :context')
            ->setParameter('token', $token)
            ->setParameter('context', $context);

        $result = $query->getQuery()->getResult();

        if (count($result))
        {
            $entity = $result[0];
            $params = unserialize($entity->getParams());

            $em->remove($entity);
            $em->flush();

            return $params;
        }

        return null;
    }

    /**
     * Removes all tokens with end of time frame.
     */
    public function garbageCollection()
    {
        $em = $this->_doctrine->getManager();

        $dateTime = new \DateTime();

        $q = $em->createQuery('delete from MetricsBundle:SecurityToken st where st.duedate < :now');
        $q->setParameter('now', $dateTime->format('Y-m-d H:i:s'));
        $numDeleted = $q->execute();
    }
}