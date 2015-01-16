<?php

namespace AppBundle\Behat;

use Doctrine\ORM\Tools\SchemaTool;
use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;

class DatabaseContext implements Context, KernelAwareContext
{
    use IsKernelAware;

    /**
     * @BeforeScenario
     */
    public function cleanDatabase()
    {
        $dbPath = sprintf('%s/%s.db', $this->getParameter('kernel.logs_dir'), $this->kernel->getEnvironment());
        file_exists($dbPath) and @unlink($dbPath);

        $em = $this->get('em');
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($metadata);
    }
}
