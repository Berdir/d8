<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Context\LazyContextRepository.
 */

namespace Drupal\Core\Plugin\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a context repository which uses context providers in the container.
 */
class LazyContextRepository implements ContextRepositoryInterface {

  /**
   * The set of available context providers service IDs.
   *
   * @var string[]
   *   Context provider service IDs.
   */
  protected $contextProviderServiceIDs = [];

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The statically cached contexts.
   *
   * @var \Drupal\Core\Plugin\Context\ContextInterface[]
   */
  protected $contexts = [];

  /**
   * Constructs a LazyContextRepository object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   * @param string[] $context_provider_service_ids
   *   The set of the available context provider service IDs.
   */
  public function __construct(ContainerInterface $container, array $context_provider_service_ids) {
    $this->container = $container;
    $this->contextProviderServiceIDs = $context_provider_service_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getRunTimeContexts(array $context_ids) {
    $contexts = [];

    // Create a map of context providers (service IDs) to context slot names.
    $context_ids_by_service = [];
    foreach ($context_ids as $id) {
      if (isset($this->contexts[$id])) {
        $contexts[$id] = $this->contexts[$id];
        continue;
      }
      // The IDs have been passed in @{service_id}:{context_slot_name} format.
      if ($id[0] === '@' && strpos($id, ':') !== FALSE) {
        list($service_id, $context_slot_name) = explode(':', $id, 2);
        // Remove the leading '@'.
        $service_id = substr($service_id, 1);
      }
      else {
        throw new \InvalidArgumentException('You must provide the context IDs in the @{service_id}:{context_slot_name} format.');
      }
      $context_ids_by_service[$service_id][] = $context_slot_name;
    }

    // Iterate over all missing context providers (services), gather the
    // run-time contexts and assign them to the slots as requested.
    foreach ($context_ids_by_service as $service_id => $context_slot_names) {
      $contexts_by_service = $this->container->get($service_id)->getRunTimeContexts($context_slot_names);

      $wanted_contexts = array_intersect_key($contexts_by_service, array_flip($context_slot_names));
      foreach ($wanted_contexts as $context_slot_name => $context) {
        $context_id = '@' . $service_id . ':' . $context_slot_name;
        $this->contexts[$context_id] = $contexts[$context_id] = $context;
      }
    }

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationTimeContexts() {
    $contexts = [];
    foreach ($this->contextProviderServiceIDs as $service_id) {
      $contexts_by_service = $this->container->get($service_id)->getConfigurationTimeContexts();
      foreach ($contexts_by_service as $context_slot_name => $context) {
        $context_id = '@' . $service_id . ':' . $context_slot_name;
        $contexts[$context_id] = $context;
      }
    }

    return $contexts;
  }

}
