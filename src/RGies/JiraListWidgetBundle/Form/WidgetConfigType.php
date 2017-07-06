<?php

namespace RGies\JiraListWidgetBundle\Form;

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
            ->add('jql_query','textarea',array('attr'=>array('placeholder'=>'type=Bug AND resolution=Unresolved ORDER BY created')))
            ->add('extended_info','choice',array(
                'choices' => array(
                    'summary' => 'Issue summary',
                    'assignee_invest' => 'Assignee + time spend',
                    'age_invest' => 'Issue age + time spend',
                    'status_age' => 'Status + issue age',
                )
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraListWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_list_widget_widgetconfig';
    }
}
