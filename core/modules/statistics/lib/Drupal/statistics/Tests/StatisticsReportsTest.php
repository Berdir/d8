<?php

/**
 * @file
 * Definition of Drupal\statistics\Tests\StatisticsReportsTest.
 */

namespace Drupal\statistics\Tests;

/**
 * Tests that report pages render properly, and that access logging works.
 */
class StatisticsReportsTest extends StatisticsTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Statistics reports tests',
      'description' => 'Tests display of statistics report pages and access logging.',
      'group' => 'Statistics'
    );
  }

  /**
   * Verifies that 'Recent hits' renders properly and displays the added hit.
   */
  function testRecentHits() {
    $this->drupalGet('admin/reports/hits');
    $this->assertText('test', 'Hit title found.');
    $this->assertText('node/1', 'Hit URL found.');
    $this->assertText('Anonymous', 'Hit user found.');
  }

  /**
   * Verifies that 'Top pages' renders properly and displays the added hit.
   */
  function testTopPages() {
    $this->drupalGet('admin/reports/pages');
    $this->assertText('test', 'Hit title found.');
    $this->assertText('node/1', 'Hit URL found.');
  }

  /**
   * Verifies that 'Top referrers' renders properly and displays the added hit.
   */
  function testTopReferrers() {
    $this->drupalGet('admin/reports/referrers');
    $this->assertText('http://example.com', 'Hit referrer found.');
  }

  /**
   * Verifies that 'Details' page renders properly and displays the added hit.
   */
  function testDetails() {
    $this->drupalGet('admin/reports/access/1');
    $this->assertText('test', 'Hit title found.');
    $this->assertText('node/1', 'Hit URL found.');
    $this->assertText('Anonymous', 'Hit user found.');
  }

  /**
   * Verifies that access logging is working and is reported correctly.
   */
  function testAccessLogging() {
    $this->drupalGet('admin/reports/referrers');
    $this->drupalGet('admin/reports/hits');
    $this->assertText('Top referrers in the past 3 days', 'Hit title found.');
    $this->assertText('admin/reports/referrers', 'Hit URL found.');
  }

  /**
   * Tests the "popular content" block.
   */
  function testPopularContentBlock() {
    // Clear the block cache to load the Statistics module's block definitions.
    $manager = $this->container->get('plugin.manager.block');
    $manager->clearCachedDefinitions();

    // Visit a node to have something show up in the block.
    $node = $this->drupalCreateNode(array('type' => 'page', 'uid' => $this->blocking_user->uid));
    $this->drupalGet('node/' . $node->nid);
    // Manually calling statistics.php, simulating ajax behavior.
    $nid = $node->nid;
    $post = http_build_query(array('nid' => $nid));
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
    global $base_url;
    $stats_path = $base_url . '/' . drupal_get_path('module', 'statistics'). '/statistics.php';
    drupal_http_request($stats_path, array('method' => 'POST', 'data' => $post, 'headers' => $headers, 'timeout' => 10000));

    // Configure and save the block.
    $block_id = 'statistics_popular_block';
    $default_theme = variable_get('theme_default', 'stark');
    $block = array(
      'machine_name' => $this->randomName(8),
      'region' => 'sidebar_first',
      'statistics_block_top_day_num' => 3,
      'statistics_block_top_all_num' => 3,
      'statistics_block_top_last_num' => 3,
    );
    $this->drupalPost('admin/structure/block/manage/' . $block_id . '/' . $default_theme, $block, t('Save block'));

    // Get some page and check if the block is displayed.
    $this->drupalGet('user');
    $this->assertText('Popular content', 'Found the popular content block.');
    $this->assertText("Today's", "Found today's popular content.");
    $this->assertText('All time', 'Found the all time popular content.');
    $this->assertText('Last viewed', 'Found the last viewed popular content.');

    $this->assertRaw(l($node->label(), 'node/' . $node->nid), 'Found link to visited node.');
  }
}
