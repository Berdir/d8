<?php

/**
 * @file
 * Definition of Drupal\taxonomy\Tests\LegacyTest.
 */

namespace Drupal\taxonomy\Tests;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Test for legacy node bug.
 */
class LegacyTest extends TaxonomyTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Test for legacy node bug.',
      'description' => 'Posts an article with a taxonomy term and a date prior to 1970.',
      'group' => 'Taxonomy',
    );
  }

  function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    // Create a default vocabulary named "Tags", enabled for the 'article' content type.
    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => 'Tags',
      'vid' => 'tags',
    ));
    taxonomy_vocabulary_save($vocabulary);

    $field = array(
      'field_name' => 'field_' . $vocabulary->id(),
      'type' => 'taxonomy_term_reference',
      // Set cardinality to unlimited for tagging.
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $vocabulary->id(),
            'parent' => 0,
          ),
        ),
      ),
    );
    field_create_field($field);

    $instance = array(
      'field_name' => 'field_' . $vocabulary->id(),
      'entity_type' => 'node',
      'label' => 'Tags',
      'bundle' => 'article',
      'widget' => array(
        'type' => 'taxonomy_autocomplete',
        'weight' => -4,
      ),
    );
    field_create_instance($instance);

    $this->admin_user = $this->drupalCreateUser(array('administer taxonomy', 'administer nodes', 'bypass node access'));
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Test taxonomy functionality with nodes prior to 1970.
   */
  function testTaxonomyLegacyNode() {
    // Posts an article with a taxonomy term and a date prior to 1970.
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $date = new DrupalDateTime('1969-01-01 00:00:00');
    $edit = array();
    $edit['title'] = $this->randomName();
    $edit['date[date]'] = $date->format('Y-m-d');
    $edit['date[time]'] = $date->format('H:i:s');
    $edit["body[$langcode][0][value]"] = $this->randomName();
    $edit["field_tags[$langcode]"] = $this->randomName();
    $this->drupalPost('node/add/article', $edit, t('Save and publish'));
    // Checks that the node has been saved.
    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertEqual($node->created, $date->getTimestamp(), 'Legacy node was saved with the right date.');
  }
}
