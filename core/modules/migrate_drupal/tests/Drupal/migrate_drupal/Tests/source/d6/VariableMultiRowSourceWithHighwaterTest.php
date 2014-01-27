<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\VariableMultiRowSourceWithHighwaterTest.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests variable multirow migration from D6 to D8 w/ highwater handling.
 *
 * @group migrate_drupal
 */
class VariableMultiRowSourceWithHighwaterTest extends VariableMultiRowSourceTest {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 variable multirow source + highwater',
      'description' => 'Tests D6 variable multirow source plugin with highwater handling.',
      'group' => 'Migrate Drupal',
    );
  }

  public function setUp() {
    $this->migrationConfiguration['highwaterProperty']['field'] = 'test';
    parent::setup();
  }

}
