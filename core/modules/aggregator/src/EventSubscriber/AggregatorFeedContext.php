<?php

/**
 * @file
 * Contains \Drupal\aggregator\EventSubscriber\AggregatorFeedContext.
 */

namespace Drupal\aggregator\EventSubscriber;

use Drupal\block\Event\BlockContextEvent;
use Drupal\block\EventSubscriber\BlockContextSubscriberBase;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Provides aggregator feeds as context.
 */
class AggregatorFeedContext extends BlockContextSubscriberBase {

  /**
   * The entity storage for feeds.
   *
   * @var \Drupal\aggregator\FeedStorageInterface
   */
  protected $feedStorage;

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockStorage;

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
    $this->blockStorage = $entity_manager->getStorage('block');
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function onBlockActiveContext(BlockContextEvent $event) {
    foreach ($this->blockStorage->loadByProperties(['plugin' => 'aggregator_feed_block', 'theme' => $this->themeManager->getActiveTheme()->getName()]) as $block_id => $block) {
      /** @var $block \Drupal\block\Entity\Block */
      $block_plugin = $block->getPlugin();
      if ($block_plugin instanceof ContextAwarePluginInterface) {
        $contexts = $block_plugin->getContextMapping();
        // The context mapping is stored as aggregator.feed:uuid, so we're just
        // extracting the UUID so that we can load the specific feed object.
        list(, $uuid) = explode(':', $contexts['feed'], 2);
        $feeds = $this->feedStorage->loadByProperties(['uuid' => $uuid]);
        $feed = reset($feeds);
        $context = new Context(new ContextDefinition('entity:aggregator_feed'));
        $context->setContextValue($feed);
        $event->setContext('aggregator.feed:' . $feed->uuid(), $context);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onBlockAdministrativeContext(BlockContextEvent $event) {
    $feeds = $this->feedStorage->loadMultiple();
    foreach ($feeds as $feed) {
      $context = new Context(new ContextDefinition('entity:aggregator_feed', $feed->label()));
      $context->setContextValue($feed);
      $event->setContext('aggregator.feed:' . $feed->uuid(), $context);
    }
  }

}
