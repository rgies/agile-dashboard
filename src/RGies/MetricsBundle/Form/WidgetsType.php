<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WidgetsType extends AbstractType
{
    protected $_container;
    protected $_lastVisitedDashboardEntity;

    /**
     * Class constructor.
     */
    public function __construct($container, $lastVisitedDashboardEntity=null)
    {
        $this->_container = $container;
        $this->_lastVisitedDashboardEntity = $lastVisitedDashboardEntity;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $widgetPlugins = $this->_container->getParameter('widget_plugins');
        $updateIntervals = $this->_container->getParameter('update_intervals');

        $dashboardChoice = array(
            'class'     => 'MetricsBundle:Dashboard',
            'label'     => 'Dashboard',
            'property'  => 'title',
        );

        if ($this->_lastVisitedDashboardEntity) {
            $dashboardChoice['data'] = $this->_lastVisitedDashboardEntity;
        }

        $builder
            ->add('title')
            ->add('dashboard', 'entity', $dashboardChoice)
            ->add('type', 'choice', array('choices' => $widgetPlugins))
            ->add('update_interval', 'choice', array('choices' => $updateIntervals))
            ->add('enabled')
            ->add('pos', 'hidden')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\MetricsBundle\Entity\Widgets'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_metricsbundle_widgets';
    }
}
