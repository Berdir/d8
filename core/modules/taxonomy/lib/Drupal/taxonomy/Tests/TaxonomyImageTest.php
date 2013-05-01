<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Tests\TaxonomyImageTest.
 */

namespace Drupal\taxonomy\Tests;

/**
 * Provides helper methods for taxonomy terms with image fields.
 */
class TaxonomyImageTest extends TaxonomyTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('image');

  public static function getInfo() {
    return array(
      'name' => 'Taxonomy Image Test',
      'description' => 'Test the functionality of image fields.',
      'group' => 'Taxonomy',
    );
  }

  public function setUp() {
    parent::setUp();

    // Remove access content permission from registered users.
    user_role_revoke_permissions(DRUPAL_AUTHENTICATED_RID, array('access content'));

    $this->vocabulary = $this->createVocabulary();
    // Add a field instance to the vocabulary.
    $field = array(
      'field_name' => 'field_test',
      'type' => 'image',
      'settings' => array(
        'uri_scheme' => 'private',
      ),
    );
    field_create_field($field);
    $instance = array(
      'field_name' => 'field_test',
      'entity_type' => 'taxonomy_term',
      'bundle' => $this->vocabulary->id(),
      'settings' => array(),
    );
    field_create_instance($instance);
    entity_get_display('taxonomy_term', $this->vocabulary->id(), 'default')
      ->setComponent('field_test', array(
        'type' => 'image',
        'settings' => array(),
      ))
      ->save();
  }

  public function testTaxonomyImageAccess() {
    $user = $this->drupalCreateUser(array('administer site configuration', 'administer taxonomy', 'access user profiles'));
    $this->drupalLogin($user);

    // Create a term and upload the image.
    $term = $this->createTerm($this->vocabulary);
    $uri = $term->uri();
    $files = $this->drupalGetTestFiles('image');
    $image = array_pop($files);
    $edit['files[field_test_' . LANGUAGE_NOT_SPECIFIED . '_0]'] = drupal_realpath($image->uri);
    $this->drupalPost($uri['path'] . '/edit', $edit, t('Save'));
    $term = taxonomy_term_load($term->id());
    $this->assertText(t('Updated term @name.', array('@name' => $term->label())));

    // Create a user that should have access to the file and one that doesn't.
    $access_user = $this->drupalCreateUser(array('access content'));
    $no_access_user = $this->drupalCreateUser();
    $image = file_load($term->field_test[LANGUAGE_NOT_SPECIFIED][0]['fid']);
    $this->drupalLogin($access_user);
    $this->drupalGet(file_create_url($image->uri));
    $this->assertResponse(200, 'Private image on term is accessible with right permission');

    $this->drupalLogin($no_access_user);
    $this->drupalGet(file_create_url($image->uri));
    $this->assertResponse(403, 'Private image on term not accessible without right permission');
  }

}
