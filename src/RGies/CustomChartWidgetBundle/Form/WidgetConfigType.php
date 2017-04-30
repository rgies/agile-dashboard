<?php

namespace RGies\CustomChartWidgetBundle\Form;

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
            ->add('chart_type','choice', array('choices' => array(
                'Area'=>'Area Chart (stacked)',
                'Line'=>'Line Chart',
                'Donut'=>'Pie Chart',
                'Bar'=>'Bar Chart')))
            ->add('labels', 'text', array('label' => 'Labels (Label1,Label2)'))
            ->add('dates', 'text', array('label' => 'Dates (2017-03-01,2017-04-01)'))
            ->add('datarows', 'textarea', array('label' => 'Data values (e.g. 23,45)'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\CustomChartWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'custom_chart_widget_widgetconfig';
    }
}
