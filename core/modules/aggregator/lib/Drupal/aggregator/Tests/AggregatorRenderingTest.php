<?php

/**
 * @file
 * Definition of Drupal\aggregator\Tests\AggregatorRenderingTest.
 */

namespace Drupal\aggregator\Tests;

/**
 * Tests rendering functionality in the Aggregator module.
 */
class AggregatorRenderingTest extends AggregatorTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block');

  public static function getInfo() {
    return array(
      'name' => 'Checks display of aggregator items',
      'description' => 'Checks display of aggregator items on the page.',
      'group' => 'Aggregator'
    );
  }

  /**
   * Adds a feed block to the page and checks its links.
   *
   * @todo Test the category block as well.
   */
  public function testBlockLinks() {
    // Create feed.
    $this->createSampleNodes();
    $feed = $this->createFeed();
    $this->updateFeedItems($feed, $this->getDefaultFeedItemCount());

    // Clear the block cache to load the new block definitions.
    $this->container->get('plugin.manager.block')->clearCachedDefinitions();

    // Need admin user to be able to access block admin.
    $admin_user = $this->drupalCreateUser(array(
      'administer blocks',
      'access administration pages',
      'administer news feeds',
      'access news feeds',
    ));
    $this->drupalLogin($admin_user);

    $block = $this->drupalPlaceBlock("aggregator_feed_block:{$feed->fid}", array('label' => 'feed-' . $feed->title), array('block_count' => 2));

    // Confirm that the block is now being displayed on pages.
    $this->drupalGet('node');
    $this->assertText($block->label(), 'Feed block is displayed on the page.');

    // Find the expected read_more link.
    $href = 'aggregator/sources/' . $feed->fid;
    $links = $this->xpath('//a[@href = :href]', array(':href' => url($href)));
    $this->assert(isset($links[0]), format_string('Link to href %href found.', array('%href' => $href)));

    // Visit that page.
    $this->drupalGet($href);
    $correct_titles = $this->xpath('//h1[normalize-space(text())=:title]', array(':title' => $feed->title));
    $this->assertFalse(empty($correct_titles), 'Aggregator feed page is available and has the correct title.');

    // Set the number of news items to 0 to test that the block does not show
    // up.
    $feed->block = 0;
    aggregator_save_feed((array) $feed);
    // Check that the block is no longer displayed.
    $this->drupalGet('node');
    $this->assertNoText($block->label(), 'Feed block is not displayed on the page when number of items is set to 0.');
  }

  /**
   * Creates a feed and checks that feed's page.
   */
  public function testFeedPage() {
    // Increase the number of items published in the rss.xml feed so we have
    // enough articles to test paging.
    $config = config('system.rss');
    $config->set('items.limit', 30);
    $config->save();

    // Create a feed with 30 items.
    $this->createSampleNodes(30);
    $feed = $this->createFeed();
    $this->updateFeedItems($feed, 30);

    // Check for the presence of a pager.
    $this->drupalGet('aggregator/sources/' . $feed->fid);
    $elements = $this->xpath("//ul[@class=:class]", array(':class' => 'pager'));
    $this->assertTrue(!empty($elements), 'Individual source page contains a pager.');

    // Reset the number of items in rss.xml to the default value.
    $config->set('items.limit', 10);
    $config->save();
  }
}
