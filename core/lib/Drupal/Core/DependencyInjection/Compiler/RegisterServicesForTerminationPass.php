<?php

/**
 * @file
 * Contains \Drupal\Core\DependencyInjection\Compiler\RegisterServicesForTerminationPass.
 */

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers needs_termination tagged services with service_terminator.
 */
class RegisterServicesForTerminationPass implements CompilerPassInterface {

  /**
   * Implements CompilerPassInterface::process().
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('kernel_terminate_subscriber')) {
      return;
    }

    $definition = $container->getDefinition('kernel_terminate_subscriber');

    foreach ($container->findTaggedServiceIds('needs_termination') as $id => $attributes) {

      // We must assume that the class value has been correcly filled, even if
      // the service is created by a factory.
      $class = $container->getDefinition($id)->getClass();

      $refClass = new \ReflectionClass($class);
      $interface = 'Drupal\Core\TerminationInterface';
      if (!$refClass->implementsInterface($interface)) {
        throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
      }
      $definition->addMethodCall('registerService', array($id));
    }
  }
}
