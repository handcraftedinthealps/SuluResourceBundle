<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Middleware;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Marker stamp to disable DoctrineFlushMiddleware for envelopes with this stamp.
 */
class DisableFlushStamp implements StampInterface
{
}
