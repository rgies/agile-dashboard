<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
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
        $roles = $this->_container->getParameter('user_role_labels');

        $builder
            ->add('username')
            ->add('password', 'password', array('attr' => array('minlength'=>6)))
            ->add('firstname')
            ->add('lastname')
            ->add('jobtitle', 'text', array('required' => false))
            ->add('role', 'choice', array('choices' => $roles, 'label' => 'Access level'))
            ->add('email', 'email')
            ->add('is_active', 'checkbox', array(
                'required' => false,
                'attr' => array('checked' => 'checked'),
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\MetricsBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_MetricsBundle_user';
    }
}
