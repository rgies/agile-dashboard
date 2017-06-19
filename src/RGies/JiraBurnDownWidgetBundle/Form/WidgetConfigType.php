<?php

namespace RGies\JiraBurnDownWidgetBundle\Form;

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
            ->add('calc_base','choice', array('attr'=>array('style'=>'width: 250px'),'label'=> 'Calculation base','choices' => array(
                'count'   =>  'Issue Count',
                'hours'   =>  'Time Estimates (h)',
                'points'  =>  'Custom Field',
            )))
            ->add('customField', 'hidden')
            ->add('start_date','text',array('label' => 'Start date (2017-01-15 / -7 days / -1 month)'))
            ->add('end_date','text',array())
            ->add('velocity', 'text',array('required'=>false, 'label' => '[Optional] Current team velocity per day'))
            ->add('jql_query','textarea',array('label' => 'Remaining Jql Query'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraBurnDownWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_burn_down_widget_widgetconfig';
    }
}
