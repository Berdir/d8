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
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
      'help' => '',
      'nodes' => array('page' => 'page'),
      'weight' => mt_rand(0, 10),
    ));
    $this->vocabulary->save();

    // Setup a field and instance.
    $this->field_name = drupal_strtolower($this->randomName());
    $this->field = array(
      'field_name' => $this->field_name,
      'type' => 'taxonomy_term_reference',
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $this->vocabulary->id(),
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
    );
    field_create_instance($this->instance);
    entity_get_display('node', 'page', 'full')
      ->setComponent($this->field_name, array(
        'type' => 'taxonomy_term_reference_link',
      ))
      ->save();

    // Create a time in the past for the archive.
    $time = time() - 3600;

    for ($i = 0; $i <= 10; $i++) {
      $user = $this->drupalCreateUser();
      $term = $this->createTerm($this->vocabulary);

      $values = array('created' => $time, 'type' => 'page');
      $values[$this->field_name][LANGUAGE_NOT_SPECIFIED][]['tid'] = $term->tid;

      // Make every other node promoted.
      if ($i % 2) {
        $values['promote'] = TRUE;
      }
      $values['body'][LANGUAGE_NOT_SPECIFIED][]['value'] = l('Node ' . 1, 'node/' . 1);

      $node = $this->drupalCreateNode($values);

      search_index($node->nid, 'node', $node->body[LANGUAGE_NOT_SPECIFIED][0]['value'], LANGUAGE_NOT_SPECIFIED);

      $comment = array(
        'uid' => $user->uid,
        'nid' => $node->nid,
        'node_type' => 'node_type_' . $node->bundle(),
      );
      entity_create('comment', $comment)->save();
    }
  }

  /**
   * Test that all Default views work as expected.
   */
  public function testDefaultViews() {
    // Get all default views.
    $controller = $this->container->get('plugin.manager.entity')->getStorageController('view');
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

        $this->assert(TRUE, format_string('View @view will be executed.', array('@view' => $view->storage->id())));
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
    $filter_formats = filter_formats();
    $format = array_pop($filter_formats);
    $term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      // Use the first available text format.
      'format' => $format->format,
      'vid' => $vocabulary->id(),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    taxonomy_term_save($term);
    return $term;
  }

}
