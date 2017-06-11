<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 27.05.17
 * Time: 23:27
 */

namespace RGies\MetricsBundle\Services;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AclService.
 *
 * @package RGies\MetricsBundle\Services
 */
class AclService
{
    protected $_accessMap;
    protected $_securityContext;
    protected $_session;

    /**
     * Class constructor.
     */
    public function __construct($accessMap, $securityContext, $session)
    {
        $this->_accessMap = $accessMap;
        $this->_securityContext = $securityContext;
        $this->_session = $session;
    }

    /**
     * Gets list of all defined security roles.
     *
     * @param $path
     * @return mixed
     */
    protected function _getRoles($path)
    {
        //build a request based on path to check access
        $request = Request::create($path,'GET');

        list($roles, $channel) = $this->_accessMap->getPatterns($request);

        return $roles;
    }

    /**
     * Checks if current user has access to given url path.
     *
     * @param $path
     * @return bool
     */
    public function userHasUrlAccess($path)
    {
        $pieces = parse_url($path);
        $path = substr($pieces['path'], strrpos(strstr($pieces['path'], '.', true), '/'));
        $path = rtrim(str_replace(array('/app.php', '/app_dev.php'), '', $path), '/');

        //your logic for check access. can returns true or false
        $finalPaths = array();

        $roles = $this->_getRoles($path);

        if (count($roles)) {
            foreach($roles as $role){
                if ($this->_securityContext->isGranted($role)) {
                    $finalPaths[] = $path;
                    break;
                }
            }
        }

        return (count($finalPaths)) ? true : false;
    }

    /**
     * Checks if current user has access to given entity.
     *
     * @param $entity
     * @return bool
     */
    public function userHasEntityAccess($entity)
    {
        if (property_exists($entity, 'getRole')) {
            $role = $entity->getRole();
        } else {
            $role = 'IS_AUTHENTICATED_ANONYMOUSLY';
        }

        if ($entity->getDomain() != $this->_session->get('domain')
            && !$this->_securityContext->isGranted('ROLE_ALLOWED_TO_SWITCH')
            || !$this->_securityContext->isGranted($role)) {
            return false;
        }

        return true;
    }

}
