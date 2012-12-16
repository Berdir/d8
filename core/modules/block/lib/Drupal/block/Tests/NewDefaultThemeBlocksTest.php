<?php

/**
 * @file
 * Definition of Drupal\block\Tests\NewDefaultThemeBlocksTest.
 */

namespace Drupal\block\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test blocks correctly initialized when picking a new default theme.
 */
class NewDefaultThemeBlocksTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block');

  public static function getInfo() {
    return array(
      'name' => 'New default theme blocks',
      'description' => 'Checks that the new default theme gets blocks.',
      'group' => 'Block',
    );
  }

  /**
   * Check the enabled Bartik blocks are correctly copied over.
   */
  function testNewDefaultThemeBlocks() {
    // Create administrative user.
    $admin_user = $this->drupalCreateUser(array('administer themes'));
    $this->drupalLogin($admin_user);

    // Ensure no other theme's blocks are in the block table yet.
    $themes = array();
    $themes['default'] = variable_get('theme_default', 'stark');
    if ($admin_theme = config('system.theme')->get('admin')) {
      $themes['admin'] = $admin_theme;
    }
    $count = db_query_range('SELECT 1 FROM {block} WHERE theme NOT IN (:themes)', 0, 1, array(':themes' => $themes))->fetchField();
    $this->assertFalse($count, 'Only the default theme and the admin theme have blocks.');

    // Populate list of all blocks for matching against new theme.
    $blocks = array();
    $result = db_query('SELECT * FROM {block} WHERE theme = :theme', array(':theme' => $themes['default']));
    foreach ($result as $block) {
      // $block->theme and $block->bid will not match, so remove them.
      unset($block->theme, $block->bid);
      $blocks[$block->module][$block->delta] = $block;
    }

    // Turn on a new theme and ensure that it contains all of the blocks
    // the default theme had.
    $new_theme = 'bartik';
    theme_enable(array($new_theme));
    variable_set('theme_default', $new_theme);
    $result = db_query('SELECT * FROM {block} WHERE theme = :theme', array(':theme' => $new_theme));
    foreach ($result as $block) {
      unset($block->theme, $block->bid);
      $this->assertEqual($blocks[$block->module][$block->delta], $block, format_string('Block %name matched', array('%name' => $block->module . '-' . $block->delta)));
    }
  }
}
