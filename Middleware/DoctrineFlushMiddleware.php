<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\Common\DisableInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\Common\DisableTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class DoctrineFlushMiddleware implements MiddlewareInterface, DisableInterface
{
    use DisableTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var int
     */
    private $messageDepth = 0;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->isDisabled()) {
            return $stack->next()->handle($envelope, $stack);
        }

        ++$this->messageDepth;

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } finally {
            // need to decrease message depth in every case to start handling of next message at depth 0
            --$this->messageDepth;
        }

        // flush unit-of-work to the database after the root message was handled successfully
        if (0 === $this->messageDepth) {
            $this->entityManager->flush();
        }

        return $envelope;
    }
}
