<?php

/**
 * @file
 * Contains \Drupal\language\LanguageServiceProvider.
 */

namespace Drupal\language;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the language_manager service to point to language's module one.
 */
class LanguageServiceProvider implements ServiceProviderInterface, ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) { }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('language_manager');
    $definition->setClass('Drupal\language\LanguageManager')
      ->addArgument(new Reference('config.storage'));
  }

}
