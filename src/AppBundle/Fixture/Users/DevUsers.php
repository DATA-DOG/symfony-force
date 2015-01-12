<?php

namespace AppBundle\Fixture\Users;

use AppBundle\Fixture\FixtureInterface;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DevUsers implements FixtureInterface, ContainerAwareInterface
{
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
    public function supports($environment)
    {
        return in_array($environment, ['dev']);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // may need some groups or over related stuff created before
    }

    /**
     * @param ObjectManager $em
     */
    function load(ObjectManager $em)
    {
        $faker = Factory::create();
        $users = [
            'master' => ['ROLE_ADMIN'],
            'luke' => ['ROLE_USER'],
        ];
        foreach ($users as $username => $roles) {
            $user = new User();
            $user->setFirstname($faker->firstname);
            $user->setLastname($faker->lastname);
            $user->setEmail($username . '@datadog.lt');
            $user->setRoles($roles);

            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword('S3cretpassword', $user->getSalt()));

            $em->persist($user);
        }
        $em->flush();
    }
}
