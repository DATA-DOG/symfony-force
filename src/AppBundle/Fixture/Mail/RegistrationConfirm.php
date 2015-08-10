<?php

namespace AppBundle\Fixture\Users;

use AppBundle\Entity\MailTemplate;
use AppBundle\Fixture\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegistrationConfirm implements FixtureInterface, ContainerAwareInterface
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
        return in_array($environment, ['dev', 'prod']);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // may need some groups or over related stuff created before
    }

    public function load(ObjectManager $manager)
    {
        $emails = [
            'activate_email' => [
                'subject'=>'Activate Email',
                'content'=>'click here <a href="{{ link }}">{{ link }}</a>',
            ],
        ];

        foreach ($emails as $alias => $emailData) {
            $email = new MailTemplate();
            $email->setAlias($alias);
            $email->setSubject($emailData['subject']);
            $email->setContent($emailData['content']);
            $manager->persist($email);
        }

        $manager->flush();
    }
}
