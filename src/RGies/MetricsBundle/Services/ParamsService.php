<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 04.05.17
 * Time: 16:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Params;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class ParamsService.
 *
 * @package RGies\MetricsBundle\Services
 */
class ParamsService
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
     * Inserts parameters on placeholders at the given string.
     *
     * @param $widgetId
     * @param $string
     * @return string
     */
    public function resolveParameters($widgetId, $string)
    {
        $em = $this->_doctrine->getManager();

        $widget = $em->getRepository('MetricsBundle:Widgets')->find($widgetId);
        $entities = $em->getRepository('MetricsBundle:Params')->findBy(
            array('dashboard' => $widget->getDashboard()->getId())
        );

        foreach ($entities as $entity) {
            $placeholder = '%' . $entity->getPlaceholder() . '%';
            $string = str_replace($placeholder, $this->getValue($entity), $string);
        }

        return $string;
    }

    /**
     * Gets the correct param value by defined type.
     *
     * @param Params $param
     * @return string
     */
    public function getValue(Params $param)
    {
        $value = '';

        switch ($param->getType())
        {
            case 'date':
                if ($param->getValue() == '' && $param->getPreset()) {
                    $date = date_create($param->getPreset());
                    if ($date) {
                        $value = $date->format('Y-m-d');
                    }
                } else {
                    $value = $param->getValue();
                }
                break;
            default:
                $value = ($param->getValue()) ? $param->getValue() : $param->getPreset();
        }

        return $value;
    }

}