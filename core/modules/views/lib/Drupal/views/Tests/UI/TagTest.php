<?php

/**
 * @file
 * Definition of Drupal\views\tests\UI\TagTest.
 */

namespace Drupal\views\Tests\UI;

use Drupal\views\Tests\ViewUnitTestBase;

/**
 * Tests the views ui tagging functionality.
 */
class TagTest extends ViewUnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Tag',
      'description' => 'Tests the views ui tagging functionality.',
      'group' => 'Views UI',
    );
  }

  /**
   * Tests the views_ui_autocomplete_tag function.
   */
  public function testViewsUiAutocompleteTag() {
    module_load_include('inc', 'views_ui', 'admin');

    // Save 15 views with a tag.
    $tags = array();
    for ($i = 0; $i < 16; $i++) {
      $suffix = $i % 2 ? 'odd' : 'even';
      $tag = 'autocomplete_tag_test_' . $suffix . $this->randomName();
      $tags[] = $tag;
      entity_create('view', array('tag' => $tag, 'id' => $this->randomName()))->save();
    }

    // Make sure just ten results are returns.
    $result = views_ui_autocomplete_tag('autocomplete_tag_test');
    $matches = (array) json_decode($result->getContent());
    $this->assertEqual(count($matches), 10, 'Make sure the maximum amount of tag results is 10.');

    // Make sure that matching by a certain prefix works.
    $result = views_ui_autocomplete_tag('autocomplete_tag_test_even');
    $matches = (array) json_decode($result->getContent());
    $this->assertEqual(count($matches), 8, 'Make sure that only a subset is returned.');
    foreach ($matches as $tag) {
      $this->assertTrue(array_search($tag, $tags) !== FALSE, format_string('Make sure the returned tag @tag actually exists.', array('@tag' => $tag)));
    }

    // Make sure an invalid result doesn't return anything.
    $result = views_ui_autocomplete_tag($this->randomName());
    $matches = (array) json_decode($result->getContent());
    $this->assertEqual(count($matches), 0, "Make sure an invalid tag doesn't return anything.");
  }

}
