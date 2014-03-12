<?php

/**
 * @file
 * Contains \Drupal\aggregator\Tests\FeedProcessorPluginTest.
 */

namespace Drupal\aggregator\Tests;
use Drupal\aggregator\Entity\Feed;
use Drupal\aggregator\Entity\Item;

/**
 * Tests feed processing in the Aggregator module.
 *
 * @see \Drupal\aggregator_test\Plugin\aggregator\processor\TestProcessor.
 */
class FeedProcessorPluginTest extends AggregatorTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Feed processor plugins',
      'description' => 'Test the processor plugins functionality and discoverability.',
      'group' => 'Aggregator',
    );
  }

  /**
   * Overrides \Drupal\simpletest\WebTestBase::setUp().
   */
  public function setUp() {
    parent::setUp();
    // Enable test plugins.
    $this->enableTestPlugins();
    // Create some nodes.
    $this->createSampleNodes();
  }

  /**
   * Test processing functionality.
   */
  public function testProcess() {
    $feed = $this->createFeed();
    $this->updateFeedItems($feed);
    foreach ($feed->items as $iid) {
      $item = Item::load($iid);
      $this->assertTrue(strpos($item->label(), 'testProcessor') === 0);
    }
  }

  /**
   * Test removing functionality.
   */
  public function testRemove() {
    $feed = $this->createFeed();
    $this->updateAndRemove($feed, NULL);
    // Make sure the feed title is changed.
    $entities = entity_load_multiple_by_properties('aggregator_feed', array('description' => $feed->description->value));
    $this->assertTrue(empty($entities));
  }

  /**
   * Test post-processing functionality.
   */
  public function testPostProcess() {
    $feed = $this->createFeed(NULL, array('refresh' => 1800));
    $this->updateFeedItems($feed);
    // Reload the feed to get new values.
    \Drupal::entityManager()->getStorageController('aggregator_feed')->resetCache();
    $feed = Feed::load($feed->id());
    // Make sure its refresh rate doubled.
    $this->assertEqual($feed->getRefreshRate(), 3600);
  }
}
