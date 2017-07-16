<?php

namespace RGies\MetricsBundle\Form;

use RGies\MetricsBundle\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
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
        $widgetPlugins = $this->_container->getParameter('widget_plugins');

        $ratingChoice = array(
            '5.0'=>'5.0',
            '4.5'=>'4.5',
            '4.0'=>'4.0',
            '3.5'=>'3.5',
            '3.0'=>'3.0',
            '2.5'=>'2.5',
            '2.0'=>'2.0',
            '1.5'=>'1.5',
            '1.0'=>'1.0',
        );

        $builder
            ->add('title')
            ->add('type', 'choice', array(
                'choices'   => array('dashboard'=>'Dashboard', 'widget'=>'Widget'),
                'attr'      => array('style' => 'width:100px')
            ))
            ->add('bundle_name', 'hidden', array())
            ->add('rating', 'choice' ,array(
                'choices'   => $ratingChoice,
                'attr'      => array('style' => 'width:60px')
            ))
            ->add('description')
            ->add('image_url', 'file', array(
                'required' => false,
                'label' => 'PNG-Image (*.png)'
            ))
            ->add('json_config', 'file', array(
                'required' => false
            ))
            ->add('enabled')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Recipe::class,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_metricsbundle_recipe';
    }
}
