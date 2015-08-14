<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Internal\Fixture;

class FixturesCommand extends DoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('app:fixtures')
            ->setDescription('Load data fixtures to your database.')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command loads data fixtures from your bundles:
It loads only ones which were not appended already.

<info>php %command.full_name%</info>
<info>php %command.full_name% --env=prod</info>

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager($input->getOption('em'));

        $alreadyLoaded = $em->getRepository("AppBundle:Internal\Fixture")->findAll();
        // may support more managers
        $loaded = $this->loadFixtures($output, $em, $alreadyLoaded);

        foreach ($loaded as $fixture) {
            $added = new Fixture();
            $added->setName(get_class($fixture));
            $em->persist($added);
        }
        $em->flush();
    }

    private function loadFixtures(OutputInterface $output, EntityManager $em, array $loaded)
    {
        $logger = function($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        };
        $executor = new ORMExecutor($em);

        $paths = [];
        foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
            $paths[] = $bundle->getPath() . '/Fixture';
        }

        $loader = new DataFixturesLoader($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }
        $env = $this->getContainer()->getParameter('kernel.environment');
        $output->writeln("Loading <comment>fixtures</comment>...");

        $has = array_map(function(Fixture $fixture) {
            return $fixture->getName();
        }, $loaded);

        $fixtures = array_filter($loader->getFixtures(), function(FixtureInterface $fixture) use($has) {
            return !in_array(get_class($fixture), $has);
        });

        if (!$fixtures) {
            $output->writeln("  Could not find any new <comment>fixtures</comment> to load..");
            return [];
        }

        $executor->setLogger($logger);
        $executor->execute($fixtures, true);
        return $fixtures;
    }
}
