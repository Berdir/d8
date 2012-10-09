<?php

/**
 * @file
 * Definition of Drupal\Core\DependencyInjection\Compiler\RegisterKernelListenersPass.
 */

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterCacheBinByTagCompilerPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    $container->setParameter('cache.available-backends', array_keys(
      $container->findTaggedServiceIds('cache.backend')));
  }

}
