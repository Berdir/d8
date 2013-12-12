<?php

/**
 * @file
 * Definition of Drupal\search\Tests\SearchFieldsTest.
 */

namespace Drupal\search\Tests;

/**
 * Tests that fields and display modes work properly in Node search.
 */
class SearchFieldsTest extends SearchTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('search', 'field_ui');

  /**
   * Node used for testing.
   */
  public $node;

  public static function getInfo() {
    return array(
      'name' => 'Search fields and display modes',
      'description' => 'Verifies that fields can be managed with display modes for indexing, excerpts, and display',
      'group' => 'Search',
    );
  }

  function setUp() {
    parent::setUp();

    // Create a new content type.
    $type = 'sft_bundle';
    $this->drupalCreateContentType(array('type' => $type, 'name' => $type));

    // Create a test user who can manage content types and fields.
    $test_user = $this->drupalCreateUser(array('access content', 'search content', 'administer nodes', 'administer content types', 'administer node fields', 'administer node display', 'create sft_bundle content', 'access site reports', 'administer site configuration'));
    $this->drupalLogin($test_user);

    // Add some text fields to the content type (default settings).
    $fields = array('for_index', 'for_excerpt', 'for_display');
    foreach ($fields as $field) {
      $this->drupalPostForm('admin/structure/types/manage/' . $type . '/fields',
        array(
          'fields[_add_new_field][label]' => $field,
          'fields[_add_new_field][type]' => 'text',
          'fields[_add_new_field][field_name]' => $field,
        ), t('Save'));
      $this->drupalPostForm(NULL, array(), t('Save field settings'));
      $this->drupalPostForm(NULL, array(), t('Save settings'));
    };

    // Configure the Search display modes to each show only one of the three
    // fields.
    $this->drupalPostForm('admin/structure/types/manage/' . $type . '/display',
      array(
        'display_modes_custom[search_index]' => TRUE,
        'display_modes_custom[search_result]' => TRUE,
        'display_modes_custom[search_result_extra]' => TRUE,
      ), t('Save'));

    $this->drupalPostForm('admin/structure/types/manage/' . $type . '/display/search_index',
      array(
        'fields[field_for_excerpt][type]' => 'hidden',
        'fields[field_for_display][type]' => 'hidden',
      ), t('Save'));
    $this->drupalPostForm('admin/structure/types/manage/' . $type . '/display/search_result',
      array(
        'fields[field_for_index][type]' => 'hidden',
        'fields[field_for_display][type]' => 'hidden',
      ), t('Save'));
    $this->drupalPostForm('admin/structure/types/manage/' . $type . '/display/search_result_extra',
      array(
        'fields[field_for_index][type]' => 'hidden',
        'fields[field_for_excerpt][type]' => 'hidden',
      ), t('Save'));

    // Clear all caches, because this test does not appear to work otherwise.
    $this->drupalPostForm('admin/config/development/performance', array(), t('Clear all caches'));

    // Create a node with different values in the 3 fields.
    $this->drupalPostForm('node/add/' . $type,
      array(
        'title[0][value]' => 'Title header',
        'field_for_index[0][value]' => 'index contents database',
        'field_for_excerpt[0][value]' => 'excerpt snippet highlighted',
        'field_for_display[0][value]' => 'display output formatted',
      ), t('Save and publish'));
    $node = node_load(1, TRUE);
    $node = $node->getUntranslated();
    $this->node = $node;

    // Update the search index.
    $this->container->get('plugin.manager.search')->createInstance('node_search')->updateIndex();
    search_update_totals();

    $this->drupalGet('admin/reports/dblog');
  }

  /**
   * Tests that the search field display modes work correctly.
   */
  function testSearchFieldDisplays() {
    // Reality check: verify that the information is on the node.
    $this->drupalGet('node/' . $this->node->id());
    $this->assertText('index contents database', 'Index text is on the node');
    $this->assertText('excerpt snippet highlighted', 'Excerpt text is on the node');
    $this->assertText('display output formatted', 'Display text is on the node');

    // Reality check: verify that the display modes are working.
    $build = node_view($this->node, 'default');
    $out = drupal_render($build);
    $this->assertTrue(strpos($out, 'index contents database') > 0, 'Index text is present in full view mode (' . strip_tags($out) . ')');
    $this->assertTrue(strpos($out, 'excerpt snippet highlighted') > 0, 'Excerpt text is not present in full view mode');
    $this->assertTrue(strpos($out, 'display output formatted') > 0, 'Display text is not present in full view mode');

    $build = node_view($this->node, 'search_index');
    $out = drupal_render($build);
    $this->assertTrue(strpos($out, 'index contents database') > 0, 'Index text is present in index view mode (' . strip_tags($out) . ')');
    $this->assertFalse(strpos($out, 'excerpt snippet highlighted') > 0, 'Excerpt text is not present in index view mode');
    $this->assertFalse(strpos($out, 'display output formatted') > 0, 'Display text is not present in index view mode');

    $build = node_view($this->node, 'search_result');
    $out = drupal_render($build);
    $this->assertFalse(strpos($out, 'index contents database') > 0, 'Index text is not present in excerpt view mode (' . strip_tags($out) . ')');
    $this->assertTrue(strpos($out, 'excerpt snippet highlighted') > 0, 'Excerpt text is present in excerpt view mode');
    $this->assertFalse(strpos($out, 'display output formatted') > 0, 'Display text is not present in excerpt view mode');

    $build = node_view($this->node, 'search_result_extra');
    $out = drupal_render($build);
    $this->assertFalse(strpos($out, 'index contents database') > 0, 'Index text is present not in result view mode (' . strip_tags($out) . ')');
    $this->assertFalse(strpos($out, 'excerpt snippet highlighted') > 0, 'Excerpt text is not present in result view mode');
    $this->assertTrue(strpos($out, 'display output formatted') > 0, 'Display text is present in result view mode');

    // Verify that if we search for excerpt or display words, we find nothing
    // because they are not in the index.
    $this->drupalPostForm('search/node', array('keys' => 'excerpt'), t('Search'));
    $this->assertText('no results', 'Search for words not in index did not find a match');
    $this->drupalPostForm('search/node', array('keys' => 'display'), t('Search'));
    $this->assertText('no results', 'Search for words not in index did not find a match');

    // Verify that if we search for index words or node title, we find it,
    // and that the display words are shown.
    $this->drupalPostForm('search/node', array('keys' => 'index'), t('Search'));
    $this->assertNoText('no results', 'Search for words in index did find a match');
    $this->assertText($this->node->label(), 'Node title is displayed');
    $this->assertText('display output formatted', 'Display field is displayed');

    $this->drupalPostForm('search/node', array('keys' => $this->node->label()), t('Search'));
    $this->assertNoText('no results', 'Search for node title did find a match');
    $this->assertText('display output formatted', 'Display field is displayed');

    // Verify that if we search for index words with an excerpt and display
    // word also OR'd into the search string, the excerpt word is highlighted
    // but not the index or display word. Also verify that all the display words
    // are shown, and the index words not in the query are missing.
    $this->drupalPostForm('search/node', array('keys' => 'excerpt OR index OR display'), t('Search'));
    $this->assertText('display output formatted', 'Display field is displayed');
    $this->assertText($this->node->label(), 'Node title is displayed');
    $this->assertRaw('<strong>excerpt</strong>', 'Excerpt word is highlighted');
    $this->assertNoRaw('<strong>display</strong>', 'Display word is not highlighted');
    $this->assertNoRaw('<strong>index</strong>', 'Index word is not highlighted');
    $this->assertNoText('contents', 'Other index word is not present');
    $this->assertNoText('database', 'Other index word is not present');

    // Turn off the customized display of Search Results Extra.
    $this->drupalPostForm('admin/structure/types/manage/sft_bundle/display',
      array(
        'display_modes_custom[search_result_extra]' => FALSE,
      ), t('Save'));
    // Verify that the display stuff is now not displayed in search results.
    $this->drupalPostForm('search/node', array('keys' => 'index'), t('Search'));
    $this->assertNoText('display output formatted', 'Display field is not displayed');
    $this->assertText($this->node->label(), 'Node title is displayed');
    $this->assertNoText('no results', 'Search for words in index did find a match');

  }
}
