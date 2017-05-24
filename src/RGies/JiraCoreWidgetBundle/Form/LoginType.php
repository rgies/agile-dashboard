<?php

namespace RGies\JiraCoreWidgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host','text', array('attr'=>array('placeholder'=>'https://xxx.atlassian.net')))
            ->add('user', 'text', array('attr'=>array('placeholder'=>'E-mail or login name')))
            ->add('password','password')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*
        $resolver->setDefaults(array(
            'data_class' => ''
        ));
        */
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_jiracorewidgetbundle_login';
    }
}
