<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;

interface DoctrineNestedListRepresentationFactoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $parameters
     * @param int|string|null $parentId
     * @param int[]|string[] $expandedIds
     * @param string[] $includedFields
     * @param string[] $groupByFields
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        $parentId = null,
        array $expandedIds = [],
        array $includedFields = [],
        array $groupByFields = [],
        string $listKey = null
    ): CollectionRepresentation;
}
