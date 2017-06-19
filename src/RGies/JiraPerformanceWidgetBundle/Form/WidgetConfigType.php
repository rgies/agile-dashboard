<?php

namespace RGies\JiraPerformanceWidgetBundle\Form;

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
        $icons = array_flip($this->_container->getParameter('widget_icons'));

        $builder
            ->add('widget_id','hidden')
            ->add('icon', 'choice', array('choices' => $icons))
            ->add('customField', 'text', array('label' => 'Story Points Field (e.g. customfield_10004)'))
            ->add('extended_info','choice',array(
                'required' => false,
                'choices' => array(
                    '' => 'Issue count',
                    'velocity' => 'Calculated velocity',
                )
            ))
            ->add('jql_query','text',array('attr'=>array('placeholder'=>'project=PI and resolutiondate>=startOfDay(-7) and type=Bug')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\JiraPerformanceWidgetBundle\Entity\WidgetConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jira_performance_widget_widgetconfig';
    }
}
