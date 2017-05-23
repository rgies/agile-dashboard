<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 10.05.17
 * Time: 8:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Credential;
use Symfony\Component\Config\Definition\Exception\Exception;

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

    private $_secret;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $secret)
    {
        $this->_doctrine = $doctrine;
        $this->_secret = $secret;
    }

    /**
     * Save credential value.
     *
     * @param string $domain Component (Jira, Google, etc.)
     * @param object|array $data   Credential array (e.g. login, password)
     */
    public function saveCredentials($domain, $data)
    {
        $em = $this->_doctrine->getManager();

        $encryption_key = $this->_secret;
        $pure_string = serialize($data);

        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = base64_encode(
            mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv)
        );

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy(
            array('domain' => $domain)
        );

        if ($entities) {
            $credential = $entities[0];
            $credential -> setValue($encrypted_string) -> setUpdated(time());
        } else {
            $credential = new Credential();
            $credential -> setDomain($domain)
                -> setValue($encrypted_string)
                -> setUpdated(time())
                -> setCreated(time());
        }

        $em->persist($credential);
        $em->flush();
    }

    /**
     * Loads credentials value.
     *
     * @param $domain
     * @return object|array|null
     */
    public function loadCredentials($domain)
    {
        $em = $this->_doctrine->getManager();
        $encryption_key = $this->_secret;
        $credentials = null;

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy(
            array('domain' => $domain)
        );

        if ($entities) {
            $entity = $entities[0];
            $encrypted_string = base64_decode($entity->getValue());

            $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);

            $credentials = unserialize($decrypted_string);
        }

        return $credentials;
    }

}