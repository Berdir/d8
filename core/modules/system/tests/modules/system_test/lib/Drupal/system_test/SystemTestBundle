<?php

/**
 * @file
 * Definition of Drupal\system_test\SystemTestBundle.
 */

namespace Drupal\system_test;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle class for the overlay module.
 */
class SystemTestBundle extends Bundle {

  /**
   * Implements \Symfony\Component\HttpKernel\Bundle\BundleInterface::build().
   */
  public function build(ContainerBuilder $container) {
    $container->register('system_test_close_subscriber', 'Drupal\system_test\EventSubscriber\SystemTestCloseSubscriber')
      ->addTag('event_subscriber');
  }

}
