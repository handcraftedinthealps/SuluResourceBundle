<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class DoctrineFlushMiddleware implements MiddlewareInterface
{
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

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        ++$this->messageDepth;

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } finally {
            // need to decrease message depth in every case to start handling of next message at depth 0
            --$this->messageDepth;
        }

        // flush unit-of-work to the database after the root message was handled successfully
        if (0 === $this->messageDepth) {
            if (!empty($envelope->all(DisableFlushStamp::class))) {
                return $envelope;
            }

            $this->entityManager->flush();
        }

        return $envelope;
    }
}
