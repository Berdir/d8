<?php

/**
 * @file
 * Contains Drupal\statistics\StatisticsBundle.
 */

namespace Drupal\statistics;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle class for the statistics module.
 */
class StatisticsBundle extends Bundle {

  /**
   * Implements \Symfony\Component\HttpKernel\Bundle\BundleInterface::build().
   */
  public function build(ContainerBuilder $container) {
    $container->register('statistics_close_subscriber', 'Drupal\statistics\EventSubscriber\StatisticsCloseSubscriber')
      ->addTag('event_subscriber');
  }

}
