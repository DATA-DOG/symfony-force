<?php

namespace AppBundle\Entity\Internal;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="internal_fixtures")
 * @ORM\Entity
 */
class Fixture
{
    /**
     * @ORM\Column(length=255)
     * @ORM\Id
     */
    private $name;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
