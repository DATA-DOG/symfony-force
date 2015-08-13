<?php namespace AppBundle\Fixture\Cms;

use AppBundle\Entity\CmsBlock;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class HomepageBlock
 */
class HomepageBlock implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0;
    }

    public function load(ObjectManager $manager)
    {
        $block = [
            'homepage' => [
                'name'=>'Homepage',
                'content'=>'<a href="{{ url("homepage") }}">Hello world</a>',
            ],
        ];

        foreach ($block as $alias => $emailData) {
            $email = new CmsBlock();
            $email->setAlias($alias);
            $email->setName($emailData['name']);
            $email->setContent($emailData['content']);
            $manager->persist($email);
        }

        $manager->flush();
    }
}
