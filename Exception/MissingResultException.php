<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Exception;

class MissingResultException extends \Exception
{
    /**
     * @var string
     */
    private $method;

    public function __construct(string $method)
    {
        parent::__construct(\sprintf('Result is missing for method "%s"', $method));

        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
