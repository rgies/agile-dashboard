<?php

namespace RGies\JiraTimeTrackingWidgetBundle\Form;

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
            ->add('jql_query','text',array('attr'=>array('placeholder'=>'project=PI and created>=-30d')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraTimeTrackingWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_time_tracking_widget_widgetconfig';
    }
}
