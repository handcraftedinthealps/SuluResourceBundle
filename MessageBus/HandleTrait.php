<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\MessageBus;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait as SymfonyHandleTrait;

trait HandleTrait
{
    use SymfonyHandleTrait{
        handle as doHandle;
    }

    protected function handle($message)
    {
        try {
            return $this->doHandle($message);
        } catch (HandlerFailedException $exception) {
            throw $exception->getPrevious();
        }
    }
}
