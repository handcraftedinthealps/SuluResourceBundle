<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;

interface DoctrineListRepresentationFactoryInterface
{
    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     * @param string[] $includedFields
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = []
    ): PaginatedRepresentation;
}
