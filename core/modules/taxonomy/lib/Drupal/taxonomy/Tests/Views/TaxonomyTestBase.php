<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Tests\Views\TaxonomyTestBase.
 */

namespace Drupal\taxonomy\Tests\Views;

use Drupal\views\Tests\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Base class for all taxonomy tests.
 */
abstract class TaxonomyTestBase extends ViewTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('taxonomy', 'taxonomy_test_views');

  /**
   * Stores the nodes used for the different tests.
   *
   * @var array
   */
  protected $nodes = array();

  /**
   * Stores the first term used in the different tests.
   *
   * @var Drupal\taxonomy\Term
   */
  protected $term1;

  /**
   * Stores the second term used in the different tests.
   *
   * @var Drupal\taxonomy\Term
   */
  protected $term2;

  function setUp() {
    parent::setUp();
    $this->mockStandardInstall();

    ViewTestData::importTestViews(get_class($this), array('taxonomy_test_views'));

    $this->term1 = $this->createTerm();
    $this->term2 = $this->createTerm();

    $node = array();
    $node['type'] = 'article';
    $node['field_views_testing_tags'][LANGUAGE_NOT_SPECIFIED][]['tid'] = $this->term1->tid;
    $node['field_views_testing_tags'][LANGUAGE_NOT_SPECIFIED][]['tid'] = $this->term2->tid;
    $this->nodes[] = $this->drupalCreateNode($node);
    $this->nodes[] = $this->drupalCreateNode($node);
  }

  /**
   * Provides a workaround for the inability to use the standard profile.
   *
   * @see http://drupal.org/node/1708692
   */
  protected function mockStandardInstall() {
    $type = array(
      'type' => 'article',
    );

    $type = node_type_set_defaults($type);
    node_type_save($type);
    node_add_body_field($type);

    // Create the vocabulary for the tag field.
    $this->vocabulary = entity_create('taxonomy_vocabulary',  array(
      'name' => 'Views testing tags',
      'vid' => 'views_testing_tags',
    ));
    $this->vocabulary->save();
    $field = array(
      'field_name' => 'field_' . $this->vocabulary->id(),
      'type' => 'taxonomy_term_reference',
      // Set cardinality to unlimited for tagging.
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $this->vocabulary->id(),
            'parent' => 0,
          ),
        ),
      ),
    );
    field_create_field($field);
    $instance = array(
      'field_name' => 'field_' . $this->vocabulary->id(),
      'entity_type' => 'node',
      'label' => 'Tags',
      'bundle' => 'article',
      'widget' => array(
        'type' => 'taxonomy_autocomplete',
        'weight' => -4,
      ),
    );
    field_create_instance($instance);

    entity_get_display('node', 'article', 'default')
      ->setComponent($instance['field_name'], array(
        'type' => 'taxonomy_term_reference_link',
        'weight' => 10,
      ))
      ->save();
    entity_get_display('node', 'article', 'teaser')
      ->setComponent($instance['field_name'], array(
        'type' => 'taxonomy_term_reference_link',
        'weight' => 10,
      ))
      ->save();
  }

  /**
   * Returns a new term with random properties in vocabulary $vid.
   *
   * @return Drupal\taxonomy\Term
   *   The created taxonomy term.
   */
  protected function createTerm() {
    $filter_formats = filter_formats();
    $format = array_pop($filter_formats);
    $term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      // Use the first available text format.
      'format' => $format->format,
      'vid' => $this->vocabulary->id(),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    $term->save();
    return $term;
  }

}
