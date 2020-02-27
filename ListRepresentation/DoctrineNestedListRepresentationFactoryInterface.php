<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;

interface DoctrineNestedListRepresentationFactoryInterface
{
    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     * @param int|string|null $parentId
     * @param int[]|string[] $expandedIds
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        $parentId = null,
        array $expandedIds = []
    ): CollectionRepresentation;
}
