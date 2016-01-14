<?php

/**
 * @file
 * Contains \Drupal\file\Tests\FileOnTranslatedEntityTest.
 */

namespace Drupal\file\Tests;

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Uploads files to translated nodes
 *
 * @group file
 */
class FileOnTranslatedEntityTest extends FileFieldTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('language', 'content_translation');

  /**
   * The name of the file field used in the test.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create the "Basic page" node type.
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    // Create a file field on the "Basic page" node type.
    $this->fieldName = strtolower($this->randomMachineName());
    $this->createFileField($this->fieldName, 'node', 'page');

    // Create and login user.
    $permissions = array(
      'access administration pages',
      'administer content translation',
      'administer content types',
      'administer languages',
      'create content translations',
      'create page content',
      'edit any page content',
      'translate any entity',
    );
    $admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($admin_user);

    // Add a second and third language.
    $edit = array();
    $edit['predefined_langcode'] = 'fr';
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    $edit = array();
    $edit['predefined_langcode'] = 'nl';
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    // Enable translation for "Basic page" nodes.
    $edit = array(
      'entity_types[node]' => 1,
      'settings[node][page][translatable]' => 1,
      "settings[node][page][fields][$this->fieldName]" => 1,
    );
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));
    \Drupal::entityManager()->clearCachedDefinitions();
  }

  /**
   * Tests synced file fields on translated nodes.
   */
  public function testSyncedFiles() {
    // Verify that the file field on the "Basic page" node type is translatable.
    $definitions = \Drupal::entityManager()->getFieldDefinitions('node', 'page');
    $this->assertTrue($definitions[$this->fieldName]->isTranslatable(), 'Node file field is translatable.');

    // Create a default language node.
    $default_language_node = $this->drupalCreateNode(array('type' => 'page', 'title' => 'Lost in translation'));

    // Edit the node to upload a file.
    $edit = array();
    $name = 'files[' . $this->fieldName . '_0]';
    $edit[$name] = drupal_realpath($this->drupalGetTestFiles('text')[0]->uri);
    $this->drupalPostForm('node/' . $default_language_node->id() . '/edit', $edit, t('Save'));
    $first_fid = $this->getLastFileId();

    // Languages are cached on many levels, and we need to clear those caches.
    $this->rebuildContainer();

    // Translate the node into French: remove the existing file.
    $this->drupalPostForm('node/' . $default_language_node->id() . '/translations/add/en/fr', array(), t('Remove'));

    // Upload a different file.
    $edit = array();
    $edit['title[0][value]'] = 'Bill Murray';
    $name = 'files[' . $this->fieldName . '_0]';
    $edit[$name] = drupal_realpath($this->drupalGetTestFiles('text')[1]->uri);
    $this->drupalPostForm(NULL, $edit, t('Save (this translation)'));
    // This inspects the HTML after the post of the translation, the file
    // should be displayed on the original node.
    $this->assertRaw('file--mime-text-plain');
    $second_fid = $this->getLastFileId();

    /* @var $file \Drupal\file\FileInterface */

    // Ensure the file status of the first file permanent.
    $file = File::load($first_fid);
    $this->assertTrue($file->isPermanent());

    // Ensure the file status of the second file is permanent.
    $file = File::load($second_fid);
    $this->assertTrue($file->isPermanent());

    // Languages are cached on many levels, and we need to clear those caches.
    $this->rebuildContainer();

    // Translate the node into dutch: remove the existing file.
    $this->drupalPostForm('node/' . $default_language_node->id() . '/translations/add/en/nl', array(), t('Remove'));

    // Upload a different file.
    $edit = array();
    $edit['title[0][value]'] = 'Scarlett Johansson';
    $name = 'files[' . $this->fieldName . '_0]';
    $edit[$name] = drupal_realpath($this->drupalGetTestFiles('text')[2]->uri);
    $this->drupalPostForm(NULL, $edit, t('Save (this translation)'));
    $third_fid = $this->getLastFileId();

    // Ensure the first file still exists. Calling isPermanent() would crash
    // the test suite.
    $file = File::load($first_fid);
    $this->assertTrue(!empty($file) && $file->isPermanent(), 'First file still exists and is permanent.');
    // This inspects the HTML after the post of the translation, the file
    // should be displayed on the original node.
    $this->assertRaw('file--mime-text-plain');

    // Ensure the file status of the second file is permanent.
    $file = File::load($second_fid);
    $this->assertTrue($file->isPermanent());

    // Ensure the file status of the third file is permanent.
    $file = File::load($third_fid);
    $this->assertTrue($file->isPermanent());

  }

}
