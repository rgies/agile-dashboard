<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JiraCountConfigType extends AbstractType
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
            ->add('icon', 'choice', array('choices' => $icons))
            ->add('jql_query')
            ->add('history')
            //->add('happy_expression')
            //->add('unhappy_expression')
            ->add('widget_id', 'hidden')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\MetricsBundle\Entity\JiraCountConfig'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_metricsbundle_jiracountconfig';
    }
}
