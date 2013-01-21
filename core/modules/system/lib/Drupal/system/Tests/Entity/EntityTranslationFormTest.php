<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Entity\EntityTranslationFormTest.
 */

namespace Drupal\system\Tests\Entity;

use Exception;
use InvalidArgumentException;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Language\Language;

/**
 * Tests entity translation form.
 */
class EntityTranslationFormTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_test', 'locale', 'node');

  protected $langcodes;

  public static function getInfo() {
    return array(
      'name' => 'Entity translation form',
      'description' => 'Tests entity translation form functionality.',
      'group' => 'Entity API',
    );
  }

  function setUp() {
    parent::setUp();
    // Enable translations for the test entity type.
    state()->set('entity_test.translation', TRUE);

    // Create test languages.
    $this->langcodes = array();
    for ($i = 0; $i < 2; ++$i) {
      $language = new Language(array(
        'langcode' => 'l' . $i,
        'name' => $this->randomString(),
      ));
      $this->langcodes[$i] = $language->langcode;
      language_save($language);
    }
  }

  /**
   * Tests entity form language.
   */
  function testEntityFormLanguage() {
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    $web_user = $this->drupalCreateUser(array('create page content', 'edit own page content', 'administer content types'));
    $this->drupalLogin($web_user);

    // Create a node with language LANGUAGE_NOT_SPECIFIED.
    $edit = array();
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $edit["title"] = $this->randomName(8);
    $edit["body[$langcode][0][value]"] = $this->randomName(16);

    $this->drupalGet('node/add/page');
    $form_langcode = variable_get('entity_form_langcode', FALSE);
    $this->drupalPost(NULL, $edit, t('Save'));

    $node = $this->drupalGetNodeByTitle($edit["title"]);
    $this->assertTrue($node->langcode == $form_langcode, 'Form language is the same as the entity language.');

    // Edit the node and test the form language.
    $this->drupalGet($this->langcodes[0] . '/node/' . $node->nid . '/edit');
    $form_langcode = variable_get('entity_form_langcode', FALSE);
    $this->assertTrue($node->langcode == $form_langcode, 'Form language is the same as the entity language.');

    // Explicitly set form langcode.
    $langcode = $this->langcodes[0];
    entity_get_form($node, 'default', $langcode);
    $form_langcode = variable_get('entity_form_langcode', FALSE);
    $this->assertTrue($langcode == $form_langcode, 'Form language is the same as the language parameter.');

    // Enable language selector.
    $this->drupalGet('admin/structure/types/manage/page');
    $edit = array('language_configuration[language_show]' => TRUE, 'language_configuration[langcode]' => LANGUAGE_NOT_SPECIFIED);
    $this->drupalPost('admin/structure/types/manage/page', $edit, t('Save content type'));
    $this->assertRaw(t('The content type %type has been updated.', array('%type' => 'Basic page')), 'Basic page content type has been updated.');

    // Create a node with language.
    $edit = array();
    $langcode = $this->langcodes[0];
    $field_langcode = LANGUAGE_NOT_SPECIFIED;
    $edit["title"] = $this->randomName(8);
    $edit["body[$field_langcode][0][value]"] = $this->randomName(16);
    $edit['langcode'] = $langcode;
    $this->drupalPost('node/add/page', $edit, t('Save'));
    $this->assertRaw(t('Basic page %title has been created.', array('%title' => $edit["title"])), 'Basic page created.');

    // Check to make sure the node was created.
    $node = $this->drupalGetNodeByTitle($edit["title"]);
    $this->assertTrue($node, 'Node found in database.');

    // Make body translatable.
    $field = field_info_field('body');
    $field['translatable'] = TRUE;
    field_update_field($field);
    $field = field_info_field('body');
    $this->assertTrue($field['translatable'], 'Field body is translatable.');

    // Create a body translation and check the form language.
    $langcode2 = $this->langcodes[1];
    $node->body[$langcode2][0]['value'] = $this->randomName(16);
    $node->save();
    $this->drupalGet($langcode2 . '/node/' . $node->nid . '/edit');
    $form_langcode = variable_get('entity_form_langcode', FALSE);
    $this->assertTrue($langcode2 == $form_langcode, "Node edit form language is $langcode2.");
  }
}
