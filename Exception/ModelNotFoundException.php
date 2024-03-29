<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Exception;

class ModelNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $entity;

    /**
     * @var mixed[]
     */
    private $criteria;

    /**
     * @param mixed[] $criteria
     */
    public function __construct(string $entity, array $criteria, int $code = 0, \Throwable $previous = null)
    {
        $criteriaMessages = [];
        foreach ($criteria as $key => $value) {
            $criteriaMessages[] = \sprintf('with %s "%s"', $key, $value);
        }

        $message = \sprintf(
            'Entity "%s" with %s not found',
            $entity,
            \implode(' and ', $criteriaMessages)
        );

        parent::__construct($message, $code, $previous);

        $this->entity = $entity;
        $this->criteria = $criteria;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return mixed[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
