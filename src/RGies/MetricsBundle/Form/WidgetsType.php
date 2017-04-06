<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WidgetsType extends AbstractType
{
    protected $_container;

    /**
     * Class constructor.
     */
    public function __construct($container)
    {
        $this->_container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $widgetPlugins = $this->_container->getParameter('widget_plugins');

        $builder
            ->add('title')
            ->add('dashboard', 'entity', array(
                'class' => 'MetricsBundle:Dashboard',
                'label' => 'Dashboard',
                'property' => 'title'
            ))
            ->add('type', 'choice', array('choices' => $widgetPlugins))
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
