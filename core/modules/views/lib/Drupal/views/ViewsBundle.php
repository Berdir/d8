<?php

/**
 * @file
 * Definition of Drupal\views\ViewsBundle.
 */

namespace Drupal\views;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\views\ViewExecutable;

/**
 * Views dependency injection container.
 */
class ViewsBundle extends Bundle {

  /**
   * Overrides Symfony\Component\HttpKernel\Bundle\Bundle::build().
   */
  public function build(ContainerBuilder $container) {
    foreach (ViewExecutable::getPluginTypes() as $type) {
      $container->register("plugin.manager.views.$type", 'Drupal\views\Plugin\ViewsPluginManager')
        ->addArgument($type);
    }

    $container
      ->register('cache.views_info', 'Drupal\Core\Cache\CacheBackendInterface')
      ->setFactoryClass('Drupal\Core\Cache\CacheFactory')
      ->setFactoryMethod('get')
      ->addArgument('views_info');

    $container->register('views.views_data', 'Drupal\views\ViewsDataCache')
      ->addArgument(new Reference('cache.views_info'))
      ->addArgument(new Reference('config.factory'));
  }

}
