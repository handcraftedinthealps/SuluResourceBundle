<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\MessageBus;

use HandcraftedInTheAlps\Bundle\SuluResourceBundle\Middleware\DoctrineFlushMiddleware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

trait RegisterMessageBusTrait
{
    protected function registerMessageBus(ContainerBuilder $container, string $busId, array $middleware): void
    {
        if ($container->hasDefinition($busId)) {
            throw new \LogicException(sprintf('Message bus "%s" has already been registered!', $busId));
        }

        // We can not prepend the message bus in framework bundle as we don't want that it is accidentally the default
        // bus of a project. So we create the bus here ourselves be reimplementing the logic of the FrameworkExtension.
        // See: https://github.com/symfony/symfony/blob/v4.3.6/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L1647-L1686

        $defaultMiddleware = [
            'before' => [
                ['id' => 'add_bus_name_stamp_middleware'],
                ['id' => 'reject_redelivered_message_middleware'],
                ['id' => 'dispatch_after_current_bus'],
                ['id' => 'failed_message_processing_middleware'],
            ],
            'after' => [
                ['id' => 'send_message'],
                ['id' => 'handle_message'],
            ],
        ];

        // argument to add_bus_name_stamp_middleware
        $defaultMiddleware['before'][0]['arguments'] = [$busId];

        $middlewareConfig = array_merge($defaultMiddleware['before'], $middleware, $defaultMiddleware['after']);

        $container->setParameter(sprintf('%s.middleware', $busId), $middlewareConfig);
        $container
            ->register($busId, MessageBus::class)
            ->addArgument([])
            ->addTag('messenger.bus')
            ->setPublic(true);
        $container->registerAliasForArgument($busId, MessageBusInterface::class);
    }

    protected function registerFlushMiddleware(ContainerBuilder $container, string $middlewareId = 'doctrine_flush_middleware'): void
    {
        if ($container->hasDefinition($middlewareId)) {
            throw new \LogicException(sprintf('Middleware "%s" has already been registered!', $middlewareId));
        }

        $container
            ->register($middlewareId, DoctrineFlushMiddleware::class)
            ->addArgument(new Reference('doctrine.orm.default_entity_manager'))
            ->setPublic(true);
    }

    protected function registerMessageBusWithFlushMiddleware(ContainerBuilder $container, string $busId, string $middlewareId = 'doctrine_flush_middleware'): void
    {
        $this->registerFlushMiddleware($container, $middlewareId);

        $middleware = [
            ['id' => $middlewareId],
        ];

        $this->registerMessageBus($container, $busId, $middleware);
    }
}
