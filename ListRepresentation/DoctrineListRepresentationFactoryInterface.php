<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;

interface DoctrineListRepresentationFactoryInterface
{
    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = []
    ): PaginatedRepresentation;
}
