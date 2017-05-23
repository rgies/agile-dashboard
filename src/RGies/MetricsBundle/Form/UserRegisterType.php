<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserRegisterType extends AbstractType
{
    protected $_container;
    protected $_params;

    /**
     * Class constructor.
     */
    public function __construct($container, $params)
    {
        $this->_container = $container;
        $this->_params = $params;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($this->_params['login'])){
            $builder->add('username', 'text', array('data' => $this->_params['login']));
        }
        else {
            $builder->add('username');
        }

        $builder->add('password', 'password', array('attr' => array('minlength'=>6)));

        if (isset($this->_params['firstname'])){
            $builder->add('firstname', 'text', array('data' => $this->_params['firstname']));
        }
        else {
            $builder->add('firstname');
        }

        if (isset($this->_params['lastname'])){
            $builder->add('lastname', 'text', array('data' => $this->_params['lastname']));
        }
        else {
            $builder->add('lastname');
        }

        if (isset($this->_params['jobtitle'])){
            $builder->add('jobtitle', 'text', array('required' => false, 'data' => $this->_params['jobtitle']));
        }
        else {
            $builder->add('jobtitle', 'text', array('required' => false));
        }

        if (isset($this->_params['email'])){
            $builder->add('email', 'email', array('data' => $this->_params['email']));
        }
        else {
            $builder->add('email', 'email');
        }
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
        return 'rgies_MetricsBundle_user_register';
    }
}
