<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Context\ContextProviderInterface.
 */

namespace Drupal\Core\Plugin\Context;

/**
 * Defines an interface for providing plugin contexts.
 *
 * Implementations only need to deal with unqualified context IDs so they only
 * need to be unique in the context of a given service provider.
 *
 * The fully qualified context ID then includes the service ID:
 * @service_id:unqualified_context_id.
 */
interface ContextProviderInterface {

  /**
   * Provides the runtime contexts.
   *
   * For context-aware plugins to function correctly, all of the contexts that
   * they require must be populated with values. So this method must set a value
   * for each context that it adds. For example:
   *
   * @code
   *   // Determine a specific node to pass as context to a block.
   *   $node = ...
   *
   *   // Set that specific node as the value of the 'node' context.
   *   $context = new Context(new ContextDefinition('entity:node'));
   *   $context->setContextValue($node);
   *   return ['node' => $context];
   * @endcode
   *
   * @param string[] $unqualified_context_ids
   *   The requested context IDs. The context provider must only return contexts
   *   for those IDs.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   The determined contexts, keyed by the unqualified context_id.
   *
   * @see static::getAvailableContexts()
   */
  public function getRuntimeContexts(array $unqualified_context_ids);

  /**
   * Provides the available contexts.
   *
   * When a context aware plugin is being configured, the configuration UI must
   * know which named contexts are potentially available, but does not care
   * about the value, since the value can be different for each request, and
   * might not be available at all during the configuration UI's request.
   *
   * For example:
   * @code
   *   // During configuration, there is no specific node to pass as context.
   *   // However, inform the system that a context named 'node' is
   *   // available, and provide its definition, so that context aware plugins
   *   // can be configured to use it. When the plugin, for example a block,
   *   // needs to evaluate the context, the value of this context will be
   *   // supplied by getRuntimeContexts().
   *   $context = new Context(new ContextDefinition('entity:node'));
   *   return ['node' => $context];
   * @endcode
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   All available contexts keyed by the unqualified context ID.
   *
   * @see static::getRuntimeContext()
   */
  public function getAvailableContexts();

}
