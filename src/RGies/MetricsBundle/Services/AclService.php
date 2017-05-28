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

    /**
     * Class constructor.
     */
    public function __construct($accessMap, $securityContext)
    {
        $this->_accessMap = $accessMap;
        $this->_securityContext = $securityContext;
    }

    protected function _getRoles($path)
    {
        //build a request based on path to check access
        $request = Request::create($path,'GET');

        list($roles, $channel) = $this->_accessMap->getPatterns($request);

        return $roles;
    }

    public function userHasAccess($path)
    {

        $pieces = parse_url($path);
        //$path = substr($pieces['path'], strrpos(strstr($pieces['path'], '.', true), '/')) . $pieces['query'];
        $path = substr($pieces['path'], strrpos(strstr($pieces['path'], '.', true), '/'));
        $path = rtrim(str_replace(array('/app.php', '/app_dev.php'), '', $path), '/');

        //your logic for check access. can returns true or false
        $finalPaths = array();

        $roles = $this->_getRoles($path);

        foreach($roles as $role){
            if ($this->_securityContext->isGranted($role)) {
                $finalPaths[] = $path;
                break;
            }
        }

        return (count($finalPaths)) ? true : false;
    }

}
