<?php

namespace ApiBundle\Resource\CmsBlock;

use AppBundle\Entity\CmsBlock;
use ApiBundle\ResourceInterface;

class SingleResource implements ResourceInterface
{
    protected $block;

    public function __construct(CmsBlock $block)
    {
        $this->block = $block;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->block->getId(),
            'alias' => $this->block->getAlias(),
            'name' => $this->block->getName(),
            'content' => $this->block->getContent(),
            'createdAt' => $this->block->getCreatedAt()->getTimestamp(),
            'updatedAt' => $this->block->getUpdatedAt()->getTimestamp(),
        ];
    }
}
