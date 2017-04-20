<?php

namespace RGies\JiraHistoryWidgetBundle\Form;

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
            ->add('label1', 'text')
            ->add('jql_query1','textarea',array('label' => 'Jql Query 1 (type=Bug and created<=%date% and status was not in (Closed) on %date%)'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraHistoryWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_history_widget_widgetconfig';
    }
}
