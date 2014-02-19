<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\CommentSourceTestWithHighwater.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests comment migration from D6 to D8 w/ highwater handling.
 *
 * @group migrate_drupal
 */
class CommentSourceWithHighwaterTest extends CommentSourceTest {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 comment source + highwater',
      'description' => 'Tests D6 comment source plugin with highwater handling.',
      'group' => 'Migrate Drupal',
    );
  }

  const ORIGINAL_HIGHWATER = 1382255613;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->migrationConfiguration['highwaterProperty']['field'] = 'timestamp';
    array_shift($this->expectedResults);
    parent::setUp();
  }

}
