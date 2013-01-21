<?php

/**
 * @file
 * Contains \Drupal\telephone\TelephoneFieldTest.
 */

namespace Drupal\telephone\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the creation of telephone fields.
 */
class TelephoneFieldTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'field',
    'field_sql_storage',
    'node',
    'telephone'
  );

  protected $instance;
  protected $web_user;

  public static function getInfo() {
    return array(
      'name'  => 'Telephone field',
      'description'  => "Test the creation of telephone fields.",
      'group' => 'Field types'
    );
  }

  function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'article'));
    $this->article_creator = $this->drupalCreateUser(array('create article content', 'edit own article content'));
    $this->drupalLogin($this->article_creator);
  }

  // Test fields.

  /**
   * Helper function for testTelephoneField().
   */
  function testTelephoneField() {

    // Add the telepone field to the article content type.
    $field = array(
      'field_name' => 'field_telephone',
      'type' => 'telephone',
    );
    field_create_field($field);

    $instance = array(
      'field_name' => 'field_telephone',
      'label' => 'Telephone Number',
      'entity_type' => 'node',
      'bundle' => 'article',
    );
    field_create_instance($instance);

    entity_get_display('node', 'article', 'default')
      ->setComponent('field_telephone', array(
        'type' => 'telephone_link',
        'weight' => 1,
      ))
      ->save();

    // Test basic entery of telephone field.
    $edit = array(
      "title" => $this->randomName(),
      "field_telephone[und][0][value]" => "123456789",
    );

    $this->drupalPost('node/add/article', $edit, t('Save'));
    $this->assertRaw('<a href="tel:123456789">', 'A telephone link is provided on the article node page.');

    // Add number with a space in it. Need to ensure it is stripped.
    $edit = array(
      "title" => $this->randomName(),
      "field_telephone[und][0][value]" => "1234 56789",
    );

    $this->drupalPost('node/add/article', $edit, t('Save'));
    $this->assertRaw('<a href="tel:123456789">', 'Telephone link is output with whitespace removed.');
  }
}
