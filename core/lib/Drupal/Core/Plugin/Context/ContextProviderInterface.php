<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Context\ContextProviderInterface.
 */

namespace Drupal\Core\Plugin\Context;

/**
 * Defines an interface for providing plugin contexts.
 */
interface ContextProviderInterface {

  /**
   * Determines the available run-time contexts.
   *
   * For context-aware plugins to function correctly, all of the contexts that
   * they require must be populated with values. So this method must set a value
   * for each context that it adds. For example:
   * @code
   *   // Determine a specific node to pass as context to a block.
   *   $node = ...
   *
   *   // Set that specific node as the value of the 'node' context.
   *   $context = new Context(new ContextDefinition('entity:node'));
   *   $context->setContextValue($node);
   *   return ['node.node' => $context];
   * @endcode
   *
   * @param array $context_slot_names
   *   The needed context IDs. The context provider can decide to optimize it.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   The determined contexts.
   */
  public function getRunTimeContexts(array $context_slot_names);

  /**
   * Determines the available configuration-time contexts.
   *
   * When a context aware plugin is being configured, the configuration UI must
   * know which named contexts are potentially available, but does not care
   * about the value, since the value can be different for each request, and
   * might not be available at all during the configuration UI's request.
   *
   * For example:
   * @code
   *   // During configuration, there is no specific node to pass as context.
   *   // However, inform the system that a context named 'node.node' is
   *   // available, and provide its definition, so that context aware plugins
   *   // can be configured to use it. When the plugin, for example a block,
   *   // needs to evaluate the context, the value of this context will be
   *   // supplied by getRunTimeContexts().
   *   $context = new Context(new ContextDefinition('entity:node'));
   *   return ['node.node' => $context];
   * @endcode
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   All available contexts.
   *
   * @see static::getActiveContext()
   */
  public function getConfigurationTimeContexts();

}
