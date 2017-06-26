<?php

namespace RGies\JiraLeadTimeWidgetBundle\Form;

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
            ->add('start_date','text',array('required' => false, 'label' => 'Start date (2017-01-15 / -7 days / -1 month)'))
            ->add('end_date','text',array('required' => false))
            ->add('jql_query','textarea',array('label' => 'Jql Query (project=PI and type=Story and resolved>="%start% and resolved<="%end%")'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraLeadTimeWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_lead_time_widget_widgetconfig';
    }
}
