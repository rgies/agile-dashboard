<?php

namespace RGies\MetricsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use RGies\MetricsBundle\Entity\User;
use RGies\MetricsBundle\Entity\Domain;

class InitialData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        // init default domain
        $domain = new Domain();
        $domain->setTitle('Default');
        $domain->setIsActive(true);
        $em->persist($domain);
        $em->flush();

        // init admin user
        $userAdmin = new User();
        $userAdmin->setUsername('Admin');
        $userAdmin->setPassword('admin');
        $userAdmin->setRole($this->container->getParameter('global_admin_role'));
        $userAdmin->setJobtitle('admin');
        $userAdmin->setFirstname('Admin');
        $userAdmin->setLastname('User');
        $userAdmin->setEmail('admin@xxx.com');
        $userAdmin->setIsActive(true);
        $userAdmin->setDomain($domain->getId());
        $em->persist($userAdmin);
        $em->flush();
    }
}