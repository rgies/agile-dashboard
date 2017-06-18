<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DomainType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dashboardChoice = array();
        for ($i=1; $i<=20; $i++) {
            $dashboardChoice[$i] = $i;
        }

        $widgetChoice = array();
        for ($i=1; $i<=20; $i++) {
            $widgetChoice[$i] = $i;
        }

        $userChoice = array(
            '1'    => '1',
            '5'    => '5',
            '10'   => '10',
            '20'   => '20',
            '50'   => '50',
            '100'  => '100',
        );

        $builder
            ->add('title')
            ->add('dashboard_limit', 'choice', array(
                'choices'   => $dashboardChoice,
                'required'  => false,
                'attr'      => array('style' => 'width:60px')
            ))
            ->add('user_limit', 'choice', array(
                'choices'   => $userChoice,
                'required'  => false,
                'attr'      => array('style' => 'width:60px')
            ))
            ->add('widget_limit', 'choice', array(
                'choices'   => $widgetChoice,
                'required'  => false,
                'attr'      => array('style' => 'width:60px')
            ))
            ->add('description')
            ->add('is_active')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\MetricsBundle\Entity\Domain'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_metricsbundle_domain';
    }
}
