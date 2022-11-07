<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;

interface DoctrineListRepresentationFactoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $parameters
     * @param string[] $includedFields
     * @param string[] $groupByFields
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
        array $groupByFields = [],
        string $listKey = null
    ): PaginatedRepresentation;
}
