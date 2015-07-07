<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Context\ContextRepositoryInterface.
 */

namespace Drupal\Core\Plugin\Context;

/**
 * Provides a list of all contexts and as a list of contexts at runtime.
 *
 * Therefore it provides a ist of all available contexts, which is mostly useful
 * for configuration on forms, as well as a method to determine the congrete
 * contexts with each value, given a list of context IDs.
 */
interface ContextRepositoryInterface {

  /**
   * Gets run-time context values for the given context IDs.
   *
   * @param string[] $context_ids
   *   Fully qualified context IDs. These must be in the
   *   @service_id:unqualified_context_id format, for example
   *   node.node_route_context:node.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   The determined contexts.
   */
  public function getRuntimeContexts(array $context_ids);

  /**
   * Gets all available contexts for the purposes of configuration.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   All available contexts.
   */
  public function getAvailableContexts();

}
