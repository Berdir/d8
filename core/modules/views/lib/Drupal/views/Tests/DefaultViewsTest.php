<?php

/**
 * @file
 * Definition of Drupal\views\Tests\DefaultViewsTest.
 */

namespace Drupal\views\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\views\ViewExecutable;

/**
 * Tests for views default views.
 */
class DefaultViewsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('views', 'node', 'search', 'comment', 'taxonomy', 'block');

  /**
   * An array of argument arrays to use for default views.
   *
   * @var array
   */
  protected $viewArgMap = array(
    'backlinks' => array(1),
    'taxonomy_term' => array(1),
    'glossary' => array('all'),
  );

  public static function getInfo() {
    return array(
      'name' => 'Default views',
      'description' => 'Tests the default views provided by views',
      'group' => 'Views',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      'machine_name' => drupal_strtolower($this->randomName()),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
      'help' => '',
      'nodes' => array('page' => 'page'),
      'weight' => mt_rand(0, 10),
    ));
    taxonomy_vocabulary_save($this->vocabulary);

    // Setup a field and instance.
    $this->field_name = drupal_strtolower($this->randomName());
    $this->field = array(
      'field_name' => $this->field_name,
      'type' => 'taxonomy_term_reference',
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $this->vocabulary->machine_name,
            'parent' => '0',
          ),
        ),
      )
    );
    field_create_field($this->field);
    $this->instance = array(
      'field_name' => $this->field_name,
      'entity_type' => 'node',
      'bundle' => 'page',
      'widget' => array(
        'type' => 'options_select',
      ),
      'display' => array(
        'full' => array(
          'type' => 'taxonomy_term_reference_link',
        ),
      ),
    );
    field_create_instance($this->instance);

    // Create a time in the past for the archive.
    $time = time() - 3600;

    comment_add_default_comment_field('node', 'page');

    for ($i = 0; $i <= 10; $i++) {
      $user = $this->drupalCreateUser();
      $term = $this->createTerm($this->vocabulary);

      $values = array('created' => $time, 'type' => 'page');
      $values[$this->field_name][LANGUAGE_NOT_SPECIFIED][]['tid'] = $term->tid;
      $values['comment'][LANGUAGE_NOT_SPECIFIED][]['comment'] = COMMENT_OPEN;

      // Make every other node promoted.
      if ($i % 2) {
        $values['promote'] = TRUE;
      }
      $values['body'][LANGUAGE_NOT_SPECIFIED][]['value'] = l('Node ' . 1, 'node/' . 1);

      $node = $this->drupalCreateNode($values);

      search_index($node->nid, 'node', $node->body[LANGUAGE_NOT_SPECIFIED][0]['value'], LANGUAGE_NOT_SPECIFIED);

      $comment = array(
        'uid' => $user->uid,
        'entity_id' => $node->nid,
        'entity_type' => 'node',
        'field_name' => 'comment'
      );
      entity_create('comment', $comment)->save();
    }
  }

  /**
   * Test that all Default views work as expected.
   */
  public function testDefaultViews() {
    // Get all default views.
    $controller = entity_get_controller('view');
    $views = $controller->load();

    foreach ($views as $name => $view_storage) {
      $view = new ViewExecutable($view_storage);
      $view->initDisplay();
      foreach ($view->storage->get('display') as $display_id => $display) {
        $view->setDisplay($display_id);

        // Add any args if needed.
        if (array_key_exists($name, $this->viewArgMap)) {
          $view->preExecute($this->viewArgMap[$name]);
        }

        $this->assert(TRUE, format_string('View @view will be executed.', array('@view' => $view->storage->get('name'))));
        $view->execute();

        $tokens = array('@name' => $name, '@display_id' => $display_id);
        $this->assertTrue($view->executed, format_string('@name:@display_id has been executed.', $tokens));

        $count = count($view->result);
        $this->assertTrue($count > 0, format_string('@count results returned', array('@count' => $count)));
        $view->destroy();
      }
    }
  }

  /**
   * Returns a new term with random properties in vocabulary $vid.
   */
  function createTerm($vocabulary) {
    $term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      // Use the first available text format.
      'format' => db_query_range('SELECT format FROM {filter_format}', 0, 1)->fetchField(),
      'vid' => $vocabulary->vid,
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    taxonomy_term_save($term);
    return $term;
  }

}
