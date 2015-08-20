<?php

namespace ApiBundle\Resource;

use ApiBundle\ResourceInterface;

class VersionResource implements ResourceInterface
{
    protected $pkg;

    public function __construct(array $pkg)
    {
        $this->pkg = $pkg;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->pkg['name'],
            'version' => $this->pkg['version'],
            'description' => $this->pkg['description'],
        ];
    }
}
