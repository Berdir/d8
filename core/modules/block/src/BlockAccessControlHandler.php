<?php

/**
 * @file
 * Contains \Drupal\block\BlockAccessControlHandler.
 */

namespace Drupal\block;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access control handler for the block entity type.
 *
 * @see \Drupal\block\Entity\Block
 */
class BlockAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  use ConditionAccessResolverTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $manager;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('plugin.manager.condition'),
      $container->get('context.handler')
    );
  }

  /**
   * Constructs the block access control handler instance
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $manager
   *   The ConditionManager for checking visibility of blocks.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   */
  public function __construct(EntityTypeInterface $entity_type, ExecutableManagerInterface $manager, ContextHandlerInterface $context_handler) {
    parent::__construct($entity_type);
    $this->manager = $manager;
    $this->contextHandler = $context_handler;
  }


  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\block\BlockInterface $entity */
    if ($operation != 'view') {
      return parent::checkAccess($entity, $operation, $langcode, $account);
    }

    // Don't grant access to disabled blocks.
    if (!$entity->status()) {
      return AccessResult::forbidden()->cacheUntilEntityChanges($entity);
    }
    else {
      $contexts = $entity->getContexts();
      $conditions = [];
      $missing_context = FALSE;
      foreach ($entity->getVisibilityConditions() as $condition_id => $condition) {
        if ($condition instanceof ContextAwarePluginInterface) {
          try {
            $this->contextHandler->applyContextMapping($condition, $contexts);
          }
          catch (ContextException $e) {
            $missing_context = TRUE;
          }
        }
        $conditions[$condition_id] = $condition;
      }

      if ($missing_context) {
        // @todo Find a reliable way to avoid max-age 0 in all or more cases.
        //   Treat missing context vs. context without value as a different
        //   exception, for example.
        $access = AccessResult::forbidden()->setCacheMaxAge(0);
      }
      elseif ($this->resolveConditions($conditions, 'and') !== FALSE) {
        // Delegate to the plugin.
        $access = $entity->getPlugin()->access($account, TRUE);
      }
      else {
        $access = AccessResult::forbidden();
      }

      $this->mergeCacheabilityFromConditions($conditions, $access);

      // Ensure that access is evaluated again when the block changes.
      return $access->cacheUntilEntityChanges($entity);
    }
  }

  /**
   * Merges cacheablity metadata from the conditions
   *
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   List of visibility conditions.
   * @param \Drupal\Core\Access\AccessResult $access
   *   The access result object.
   */
  protected function mergeCacheabilityFromConditions($conditions, AccessResult $access) {
    // Add cacheability metadata from the conditions.
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        $access->addCacheTags($condition->getCacheTags());
        $access->addCacheContexts($condition->getCacheContexts());
        if ($condition->getCacheMaxAge() != Cache::PERMANENT && $condition->getCacheMaxAge() < $access->getCacheMaxAge()) {
          $access->setCacheMaxAge($condition->getCacheMaxAge());
        }
      }
    }
  }

}
