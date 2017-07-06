<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 06.07.17
 * Time: 5:45
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Dashboard;
use RGies\MetricsBundle\Entity\Widgets;
use RGies\MetricsBundle\Entity\Params;

/**
 * Class DashboardService.
 *
 * @package RGies\MetricsBundle\Services
 */
class DashboardService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $_doctrine;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $_session;

    /**
     * @var widgetService
     */
    protected $_widgetService;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $session, $widgetService)
    {
        $this->_doctrine = $doctrine;
        $this->_session = $session;
        $this->_widgetService = $widgetService;
    }

    /**
     * Creates new dashboard database entry with given data.
     *
     * @param array $data Dashboard data
     * @param string $title Dashboard title
     * @return Dashboard
     */
    public function import($data, $title)
    {
        $session = $this->_session;
        $em = $this->_doctrine->getManager();

        // persist dashboard
        $dashboard = new Dashboard($data['dashboard']);
        $dashboard->setTitle($title);
        $dashboard->setDomain($session->get('domain'));

        $em->persist($dashboard);
        $em->flush();

        // persist params
        if (isset($data['params'])) {
            foreach ($data['params'] as $param) {
                $param['dashboard'] = $dashboard;
                $param = new Params($param);
                $em->persist($param);
                $em->flush();
            }
        }

        // persist widgets
        foreach ($data['widgets'] as $widget) {
            $id = $widget['id'];
            $widget['dashboard'] = $dashboard;
            $widget = new Widgets($widget);
            $em->persist($widget);
            $em->flush();

            // persist config
            $config = $data['configs'][$id];
            $config['widget_id'] = $widget->getId();
            $this->_widgetService->setWidgetConfig($widget->getType(), $config);
        }

        return $dashboard;
    }

    /**
     * Generates data array.
     *
     * @param integer $id Dashbaord id
     * @return array
     */
    public function export($id)
    {
        $data = array();
        $em = $this->_doctrine->getManager();

        $dashboard = $em->getRepository('MetricsBundle:Dashboard')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (!$dashboard) {
            return null;
        }

        $data['dashboard'] = $dashboard[0];

        $params = $em->getRepository('MetricsBundle:Params')
            ->createQueryBuilder('p')
            ->where('p.dashboard = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $data['params'] = $params;

        $widgets = $em->getRepository('MetricsBundle:Widgets')
            ->createQueryBuilder('w')
            ->where('w.dashboard = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $data['widgets'] = $widgets;

        $data['configs'] = array();
        foreach($widgets as $widget) {
            $data['configs'][$widget['id']] =
                $this->_widgetService->getWidgetConfig($widget['type'], $widget['id'], true)
            ;
        }

        return $data;
    }
}