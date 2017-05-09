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

        $templateChoices = array(
            '' => 'Default',
            '1x1' => 'Small 1x1 (S)',
            '2x1' => 'Wide 2x1 (M)',
            'Xx1' => 'Stretched x1 (F)',
            '1x2' => 'Height 1x2 (M)',
            '2x2' => 'Large 2x2 (L)',
            '3x2' => 'X-Large 3x2 (XL)',
            'Xx2' => 'Stretched x2 (F)',
        );

        if ($this->_lastVisitedDashboardEntity) {
            $dashboardChoice['data'] = $this->_lastVisitedDashboardEntity;
        }

        $builder
            ->add('title')
            ->add('dashboard', 'entity', $dashboardChoice)
            ->add('type', 'choice', array('choices' => $widgetPlugins))
            ->add('size', 'choice', array(
                'label'=>'Widget size',
                'required' => false,
                'choices' => $templateChoices
            ))
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
