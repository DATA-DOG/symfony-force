<?php

namespace ApiBundle\Resource\CmsBlock;

use AppBundle\Entity\CmsBlock;
use ApiBundle\ResourceInterface;

class ListResource implements ResourceInterface
{
    protected $blocks;

    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    public function jsonSerialize()
    {
        $blocks = array_map(function (CmsBlock $block) {
            return (new SingleResource($block))->jsonSerialize();
        }, $this->blocks);
        return compact('blocks');
    }
}
