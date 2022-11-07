<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListBuilderInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\RestHelperInterface;

class DoctrineNestedListRepresentationFactory implements DoctrineNestedListRepresentationFactoryInterface
{
    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var DoctrineListBuilderFactory
     */
    private $listBuilderFactory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactory $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->restHelper = $restHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->entityManager = $entityManager;
    }

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
        array $groupByFields = []
    ): CollectionRepresentation {
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

        foreach ($groupByFields as $field) {
            $listBuilder->addGroupBy($fieldDescriptors[$field]);
        }

        // disable pagination to simplify tree handling and select tree related properties that are used below
        $listBuilder->limit(\PHP_INT_MAX);
        $listBuilder->addSelectField($fieldDescriptors['lft']);
        $listBuilder->addSelectField($fieldDescriptors['rgt']);
        $listBuilder->addSelectField($fieldDescriptors['parentId']);

        // collect entities of which the children should be included in the response
        $idsToExpand = \array_merge(
            [$parentId],
            $this->findIdsOnPathsBetween($fieldDescriptors['id']->getEntityName(), $parentId, $expandedIds),
            $expandedIds
        );

        // generate expressions to select only entities that are children of the collected expand-entities
        $expandExpressions = [];
        foreach ($idsToExpand as $idToExpand) {
            $expandExpressions[] = $listBuilder->createWhereExpression(
                $fieldDescriptors['parentId'],
                $idToExpand,
                ListBuilderInterface::WHERE_COMPARATOR_EQUAL
            );
        }

        if (1 === \count($expandExpressions)) {
            $listBuilder->addExpression($expandExpressions[0]);
        } elseif (\count($expandExpressions) > 1) {
            $orExpression = $listBuilder->createOrExpression($expandExpressions);
            $listBuilder->addExpression($orExpression);
        }

        return new CollectionRepresentation(
            $this->generateNestedRows($parentId, $resourceKey, $listBuilder->execute()),
            $resourceKey
        );
    }

    /**
     * @param int|string|null $startId
     * @param int[]|string[] $endIds
     *
     * @return int[]|string[]
     */
    private function findIdsOnPathsBetween(string $entityClass, $startId, array $endIds): array
    {
        // there are no paths and therefore no ids if we dont have any end-ids
        if (0 === \count($endIds)) {
            return [];
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($entityClass, 'entity')
            ->select('entity.id');

        // if this start-id is not set we want to include all paths from the root to our end-ids
        if ($startId) {
            $queryBuilder->from($entityClass, 'startEntity')
                ->andWhere('startEntity.id = :startIds')
                ->andWhere('entity.lft > startEntity.lft')
                ->andWhere('entity.rgt < startEntity.rgt')
                ->setParameter('startIds', $startId);
        }

        $queryBuilder->from($entityClass, 'endEntity')
            ->andWhere('endEntity.id IN (:endIds)')
            ->andWhere('entity.lft < endEntity.lft')
            ->andWhere('entity.rgt > endEntity.rgt')
            ->setParameter('endIds', $endIds);

        return \array_map('current', $queryBuilder->getQuery()->getScalarResult());
    }

    /**
     * @param int|string|null $parentId
     * @param mixed[] $flatRows
     *
     * @return mixed[]
     */
    private function generateNestedRows($parentId, string $resourceKey, array $flatRows): array
    {
        // add hasChildren property that is expected by the sulu frontend
        foreach ($flatRows as &$row) {
            $row['hasChildren'] = ($row['lft'] + 1) !== $row['rgt'];
        }

        // group rows by the id of their parent
        $rowsByParentId = [];
        foreach ($flatRows as &$row) {
            $rowParentId = $row['parentId'];
            if (!\array_key_exists($rowParentId, $rowsByParentId)) {
                $rowsByParentId[$rowParentId] = [];
            }
            $rowsByParentId[$rowParentId][] = &$row;
        }

        // embed children rows int their parent rows
        foreach ($flatRows as &$row) {
            $rowId = $row['id'];
            if (\array_key_exists($rowId, $rowsByParentId)) {
                $row['_embedded'] = [
                    $resourceKey => $rowsByParentId[$rowId],
                ];
            }
        }

        // remove tree related properties from the response
        foreach ($flatRows as &$row) {
            unset($row['rgt']);
            unset($row['lft']);
            unset($row['parentId']);
        }

        return $rowsByParentId[$parentId] ?? [];
    }
}
