<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;

class DoctrineListRepresentationFactory implements DoctrineListRepresentationFactoryInterface
{
    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var ListRestHelperInterface
     */
    private $listRestHelper;

    /**
     * @var DoctrineListBuilderFactory
     */
    private $listBuilderFactory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    public function __construct(
        RestHelperInterface $restHelper,
        ListRestHelperInterface $listRestHelper,
        DoctrineListBuilderFactory $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory
    ) {
        $this->restHelper = $restHelper;
        $this->listRestHelper = $listRestHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
    }

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
    ): PaginatedRepresentation {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        foreach ($includedFields as $field) {
            $listBuilder->addSelectField($fieldDescriptors[$field]);
        }

        $items = $listBuilder->execute();

        // sort the items to reflect the order of the given ids if the list was requested to include specific ids
        $requestedIds = $this->listRestHelper->getIds();
        if (null !== $requestedIds) {
            $idPositions = array_flip($requestedIds);

            usort($items, function ($a, $b) use ($idPositions) {
                return $idPositions[$a['id']] - $idPositions[$b['id']];
            });
        }

        return new PaginatedRepresentation(
            $items,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }
}
