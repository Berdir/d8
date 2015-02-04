<?php

/**
 * @file
 * Contains \Drupal\Core\DependencyInjection\Compiler\DependencySerializationTraitPass.
 */

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Sets the _serviceId property on all services.
 *
 * @see \Drupal\Core\DependencyInjection\DependencySerializationTrait
 */
class DependencySerializationTraitPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    foreach ($container->getDefinitions() as $service_id => $definition) {
      // Some services might have strings internally.
      // Given that you can just reload a service which is accessible via
      // Container::get, you need to filter out public services here.
      if (!$definition->hasTag('non_class') && $definition->isPublic()) {
        $definition->setProperty('_serviceId', $service_id);
      }
    }
  }

}
