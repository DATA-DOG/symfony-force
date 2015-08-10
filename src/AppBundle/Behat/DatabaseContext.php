<?php

namespace AppBundle\Behat;

use AppBundle\Behat\Doctrine\PlaceholderListener;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class DatabaseContext extends BaseContext
{
    /**
     * @BeforeScenario
     */
    function begin(BeforeScenarioScope $scope)
    {
        $placeholders = $scope->getEnvironment()->getContext('AppBundle\Behat\PlaceholderContext');
        $this->get('em')->getEventManager()->addEventSubscriber(new PlaceholderListener($placeholders));

        $this->get('db')->beginTransaction();
    }

    /**
     * @AfterScenario
     */
    function rollback()
    {
        $this->get('db')->rollback();
    }
}
