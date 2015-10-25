<?php

/**
 * @file
 * Contains \Drupal\rest\Tests\RESTFileValidationTest.
 */

namespace Drupal\rest\Tests;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\RoleInterface;

/**
 * Tests the validation of files attached via REST node creation.
 *
 * @group rest
 */
class RESTFileValidationTest extends RESTTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('hal', 'rest', 'entity_test', 'node', 'file');

  /**
   * The 'serializer' service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  protected function setUp() {
    parent::setUp();
    // Get the 'serializer' service.
    $this->serializer = $this->container->get('serializer');
  }

  /**
   * Tests basic file field validations when attaching to nodes.
   */
  public function testCreateNodeWithFile() {
    // Create a file which should be secret.

    // Create a test entity type that contains the secret file.
    $this->drupalCreateContentType(['type' => 'page']);

    // Create a test content type that anonymous can post.
    $this->drupalCreateContentType(['type' => 'article']);
    $this->createFileField('field_public_file', 'node', 'article', ['uri_scheme' => 'public'], ['file_extensions' => 'txt']);
    // Enables the REST service for 'node' entity type.
    $this->enableService('entity:node', 'POST');
    $permissions = ['create article content', 'restful post entity:node'];
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, $permissions);

    // Create a private file and attach it to a node.
    $this->createFileField('field_private_file', 'node', 'page', ['uri_scheme' => 'private'], ['file_extensions' => 'txt']);
    $private_files = $this->drupalGetTestFiles('text', NULL, FALSE);
    $private_file_author = $this->drupalCreateUser([]);
    $file = File::create([
      'uri' => $private_files[0]->uri,
    ]);
    $file->save();
    $node = Node::create([
      'title' => 'Test private title',
      'type' => 'page',
      'uid' => $private_file_author->id(),
      'field_private_file' => [
        0 => [
          'target_id' => $file->id(),
        ],
      ],
    ]);
    $node->save();

    $this->drupalLogin($private_file_author);
    $this->drupalGet(file_create_url($private_files[0]->uri));
    $this->assertResponse(200);
    $this->drupalLogout();
    $this->drupalGet(file_create_url($private_files[0]->uri));
    $this->assertResponse(403);

    // Verify that anonymous user file uploads fail when invalid.
    // Populate some entity properties before creating the entity.
    $entity = Node::create([
      'title' => $this->randomString(),
      'type' => 'article'
    ]);

    // Verify that user cannot create content when trying to write to fields
    // where it is not possible.

    $account = \Drupal::currentUser();
    $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);
    $this->httpRequest('entity/node', 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(403);
    // Remove fields where non-administrative users cannot write.
    $entity = $this->removeNodeFieldsForNonAdminUsers($entity);
    $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);
    $this->httpRequest('entity/node', 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(201);

    // The user should not be able to attach the private file.
    $entity = $entity->createDuplicate();
    $entity->field_public_file->target_id = $node->field_private_file->target_id;
    $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);
    $this->httpRequest('entity/node', 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(403);

    $this->drupalGet(file_create_url($private_files[0]->uri));
    $this->assertResponse(403);
  }

  /**
   * Creates a new file field.
   *
   * @param string $name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   * @param array $storage_settings
   *   A list of field storage settings that will be added to the defaults.
   * @param array $field_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function createFileField($name, $entity_type, $bundle, $storage_settings = [], $field_settings = [], $widget_settings = []) {
    $field_storage = FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $name,
      'type' => 'file',
      'settings' => $storage_settings,
      'cardinality' => !empty($storage_settings['cardinality']) ? $storage_settings['cardinality'] : 1,
    ]);
    $field_storage->save();

    $this->attachFileField($name, $entity_type, $bundle, $field_settings, $widget_settings);
    return $field_storage;
  }

  /**
   * Attaches a file field to an entity.
   *
   * @param string $name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param string $entity_type
   *   The entity type this field will be added to.
   * @param string $bundle
   *   The bundle this field will be added to.
   * @param array $field_settings
   *   A list of field settings that will be added to the defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   */
  protected function attachFileField($name, $entity_type, $bundle, $field_settings = [], $widget_settings = []) {
    FieldConfig::create([
      'field_name' => $name,
      'label' => $name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'required' => !empty($field_settings['required']),
      'settings' => $field_settings,
    ])->save();

    entity_get_form_display($entity_type, $bundle, 'default')
      ->setComponent($name, [
        'type' => 'file_generic',
        'settings' => $widget_settings,
      ])
      ->save();
    // Assign display settings.
    entity_get_display($entity_type, $bundle, 'default')
      ->setComponent($name, [
        'label' => 'hidden',
        'type' => 'file_default',
      ])
      ->save();
  }

}
