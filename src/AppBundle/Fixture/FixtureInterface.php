<?php

namespace AppBundle\Fixture;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface as BaseFixtureInterface;

interface FixtureInterface extends OrderedFixtureInterface, BaseFixtureInterface
{
    /**
     * Checks whether fixture can be loaded in $environment
     *
     * @param string $environment
     * @return boolean
     */
    function supports($environment);
}
