<?php

/**
 * @file
 * Contains \Drupal\aggregator\AggregatorBundle.
 */

namespace Drupal\aggregator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Registers aggregator module's services to the container.
 */
class AggregatorBundle extends Bundle {

  /**
   * Overrides Bundle::build().
   */
  public function build(ContainerBuilder $container) {
    $container->register('plugin.manager.aggregator.fetcher', 'Drupal\aggregator\Plugin\FetcherManager')
      ->addArgument(new Reference('container.namespaces'));
  }

}
