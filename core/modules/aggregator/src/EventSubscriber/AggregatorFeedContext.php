<?php

/**
 * @file
 * Contains \Drupal\aggregator\EventSubscriber\AggregatorFeedContext.
 */

namespace Drupal\aggregator\EventSubscriber;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Provides aggregator feeds as context.
 */
class AggregatorFeedContext implements ContextProviderInterface {

  /**
   * The entity storage for feeds.
   *
   * @var \Drupal\aggregator\FeedStorageInterface
   */
  protected $feedStorage;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs a new AggregatorFeedContext.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, ThemeManagerInterface $theme_manager) {
    $this->feedStorage = $entity_manager->getStorage('aggregator_feed');
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $ids = $this->feedStorage->getQuery()
      ->condition('uuid', $unqualified_context_ids, 'IN')
      ->execute();
    $contexts = [];
    foreach ($this->feedStorage->loadMultiple($ids) as $feed) {
      $context = new Context(new ContextDefinition('entity:aggregator_feed'));
      $context->setContextValue($feed);
      $contexts[$feed->uuid()] = $context;
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $feeds = $this->feedStorage->loadMultiple();
    $contexts = [];
    foreach ($feeds as $feed) {
      $context = new Context(new ContextDefinition('entity:aggregator_feed', $feed->label()));
      $context->setContextValue($feed);
      $contexts[$feed->uuid()] = $context;
    }
    return $contexts;
  }

}
