<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6VocabularySourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests Node Types migration from D6 to D8.
 *
 * @group migrate
 */
class D6VocabularySourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Vocabulary';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_vocabulary',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'vid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'v',
      ),
    ),
    'destinationIds' => array(
      // @todo
      'vid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $results = array(
    array(
      'vid' => 1,
      'name' => 'Tags',
      'description' => 'Tags description.',
      'help' => 1,
      'relations' => 0,
      'hierarchy' => 0,
      'multiple' => 0,
      'required' => 0,
      'tags' => 1,
      'module' => 'taxonomy',
      'weight' => 0,
      'node_types' => array('page', 'article'),
    ),
    array(
      'vid' => 2,
      'name' => 'Categories',
      'description' => 'Categories description.',
      'help' => 1,
      'relations' => 1,
      'hierarchy' => 1,
      'multiple' => 0,
      'required' => 1,
      'tags' => 0,
      'module' => 'taxonomy',
      'weight' => 0,
      'node_types' => array('article'),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 vocabulary source functionality',
      'description' => 'Tests D6 vocabulary source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->results as $row) {
      foreach ($row['node_types'] as $type) {
        $this->databaseContents['vocabulary_node_types'][] = array(
          'type' => $type,
          'vid' => $row['vid'],
        );
      }
      unset($row['node_types']);
    }
    $this->databaseContents['vocabulary'] = $this->results;
    parent::setUp();
  }

}
