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

    private $_session;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $secret, $session)
    {
        $this->_doctrine = $doctrine;
        $this->_secret = $secret;
        $this->_session = $session;
    }

    /**
     * Save credential value.
     *
     * @param string $provider Bundle provider (JiraCoreBundle, GoogleCoreBundle, etc.)
     * @param object|array $data   Credential array (e.g. login, password)
     */
    public function saveCredentials($provider, $data)
    {
        $em = $this->_doctrine->getManager();

        $domain = $this->_session->get('domain');
        $encryption_key = $this->_secret . '#' . $domain;
        $pure_string = serialize($data);

        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = base64_encode(
            mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv)
        );

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy(
            array('provider' => $provider, 'domain' => $domain)
        );

        if ($entities) {
            $credential = $entities[0];
            $credential -> setValue($encrypted_string) -> setUpdated(time());
        } else {
            $credential = new Credential();
            $credential -> setProvider($provider)
                -> setValue($encrypted_string)
                -> setDomain($domain)
                -> setUpdated(time())
                -> setCreated(time());
        }

        $em->persist($credential);
        $em->flush();
    }

    /**
     * Loads credentials value.
     *
     * @param $provider
     * @return array|null|object
     * @throws \Exception
     */
    public function loadCredentials($provider)
    {
        $em = $this->_doctrine->getManager();

        $domain = $this->_session->get('domain');
        $decryption_key = $this->_secret . '#' . $domain;
        $credentials = null;

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy(
            array('provider' => $provider, 'domain' => $domain)
        );

        if ($entities) {
            $entity = $entities[0];
            $encrypted_string = base64_decode($entity->getValue());

            $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $decryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);

            $credentials = @unserialize($decrypted_string);

            if (!$credentials) {
                throw new \Exception('Credential data not valid, please save '
                    . $provider . ' credentials again.');
            }

        }

        return $credentials;
    }

    /**
     * Checks if current domain has any credentials
     *
     * @param string provider Name of external provider bundle
     * @return bool
     */
    function hasCredentials($provider = null)
    {
        $em = $this->_doctrine->getManager();
        $search = array('domain' => $this->_session->get('domain'));

        if ($provider) {
            $search['provider'] = $provider;
        }

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy($search);

        return $entities ? true : false;
    }

    /**
     * Sets the connected state of a provider connection.
     *
     * @param string $provider
     * @param boolean $state
     * @return boolean Did the state could be set
     */
    public function setConnectedState($provider, $state)
    {
        $em = $this->_doctrine->getManager();
        $domain = $this->_session->get('domain');

        $entities = $em->getRepository('MetricsBundle:Credential')->findBy(
            array('provider' => $provider, 'domain' => $domain)
        );

        if ($entities) {
            $entity = $entities[0];

            $entity->setConnected($state);
            $em->persist($entity);
            $em->flush();

            return true;
        }

        return false;
    }

}