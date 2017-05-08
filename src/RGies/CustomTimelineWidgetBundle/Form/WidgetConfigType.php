<?php

namespace RGies\CustomTimelineWidgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WidgetConfigType extends AbstractType
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
        $builder
            ->add('widget_id','hidden')
            ->add('date')
            ->add('title', 'text', array('max_length' => 10))
            ->add('description', 'textarea', array('required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\CustomTimelineWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'custom_timeline_widget_widgetconfig';
    }
}
