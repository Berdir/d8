<?php

/**
 * @file
 * Contains Drupal\overlay\OverlayBundle.
 */

namespace Drupal\overlay;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle class for the overlay module.
 */
class OverlayBundle extends Bundle {

  /**
   * Implements \Symfony\Component\HttpKernel\Bundle\BundleInterface::build().
   */
  public function build(ContainerBuilder $container) {
    $container->register('overlay_close_subscriber', 'Drupal\overlay\EventSubscriber\OverlayCloseSubscriber')
      ->addTag('event_subscriber');
  }

}
