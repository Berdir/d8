<?php

/**
 * @file
 * Contains \Drupal\aggregator\AggregatorServiceProvider.
 */

namespace Drupal\aggregator;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides a block context event subscriber if the block module is enabled.
 */
class AggregatorServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $modules = $container->getParameter('container.modules');
    if (isset($modules['block'])) {
      $container->register('block.aggregator_feed_context', 'Drupal\aggregator\EventSubscriber\AggregatorFeedContext')
        ->addArgument(new Reference('entity.manager'))
        ->addArgument(new Reference('theme.manager'))
        ->addTag('event_subscriber');
    }
  }

}
