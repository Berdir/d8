<?php
/**
 * @file
 */

namespace Drupal\migrate\Tests;


/**
 * Tests taxonomy term migration with vocabulary filter from D6 to D8.
 *
 * @group migrate
 */
class D6TermSourceWithVocabularyFilterTest extends D6TermSourceTest {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 taxonomy term source with vocabulary filter functionality',
      'description' => 'Tests D6 taxonomy term source plugin with vocabulary filter.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->migrationConfiguration['source']['vocabulary'] = array(5);
    parent::setUp();
    $this->expectedResults = array_values(array_filter($this->expectedResults, function($result) {
      return $result['vid'] == 5;
    }));
  }
}
