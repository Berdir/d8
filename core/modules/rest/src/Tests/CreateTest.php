<?php

/**
 * @file
 * Contains \Drupal\rest\test\CreateTest.
 */

namespace Drupal\rest\Tests;

use Drupal\Component\Serialization\Json;

/**
 * Tests the creation of resources.
 *
 * @group rest
 */
class CreateTest extends RESTTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('hal', 'rest', 'entity_test');

  protected $serializer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->serializer = $this->container->get('serializer');
  }

  /**
   * Try to create a resource which is not REST API enabled.
   */
  public function testCreateResourceRestApiNotEnabled() {
    $entity_type = 'entity_test';
    $this->enableService('entity:' . $entity_type, 'POST');

    $permissions = $this->entityPermissions($entity_type, 'create');
    $permissions[] = 'restful post entity:' . $entity_type;

    // Create non-admin user.
    $account = $this->drupalCreateUser($permissions);
    $entity_values = $this->entityValues($entity_type);
    $entity = entity_create($entity_type, $entity_values);

    $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);

    $this->enableService(FALSE);
    $this->drupalLogin($account);
    $this->httpRequest('entity/entity_test', 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(404);
    $this->assertFalse(entity_load_multiple('entity_test', NULL, TRUE), 'No entity has been created in the database.');
  }

  /**
   * Tests several valid and invalid create requests for 'entity_test' entity type.
   */
  public function testCreateEntityTest() {
    $entity_type = 'entity_test';
    $this->enableService('entity:' . $entity_type, 'POST');
    // Create accounts (admin and non-admin).
    $accounts = $this->createAccountPerEntity($entity_type);

    foreach ($accounts as $key => $account) {
      $this->drupalLogin($account);
      $entity_values = $this->entityValues($entity_type);
      $entity = entity_create($entity_type, $entity_values);

      $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);

      // Create the entity over the REST API.
      $this->createEntityOverRestApi($entity_type, $serialized);
      $this->readEntityIdFromHeaderAndDb($entity_type, $entity, $entity_values);


      // Try to create an entity with an access protected field.
      // @see entity_test_entity_field_access()
      $context = ['account' => $account];
      $normalized = $this->serializer->normalize($entity, $this->defaultFormat, $context);
      $normalized['field_test_text'][0]['value'] = 'no access value';
      $this->httpRequest('entity/' . $entity_type, 'POST', $this->serializer->serialize($normalized, $this->defaultFormat, $context), $this->defaultMimeType);
      $this->assertResponse(403);
      $this->assertFalse(entity_load_multiple($entity_type, NULL, TRUE), 'No entity has been created in the database.');

      // Try to create a field with a text format this user has no access to.
      $entity->field_test_text->value = $entity_values['field_test_text'][0]['value'];
      $entity->field_test_text->format = 'full_html';
      $serialized = $this->serializer->serialize($entity, $this->defaultFormat, $context);
      $this->httpRequest('entity/' . $entity_type, 'POST', $serialized, $this->defaultMimeType);
      $this->assertResponse(422);
      $this->assertFalse(entity_load_multiple($entity_type, NULL, TRUE), 'No entity has been created in the database.');

      // Restore the valid test value.
      $entity->field_test_text->format = 'plain_text';
      $serialized = $this->serializer->serialize($entity, $this->defaultFormat, $context);

      $this->createEntityInvalidData($entity_type);

      $this->createEntityNoData($entity_type);

      $this->createEntityInvalidSerialized($entity, $entity_type);

      $this->createEntityWithoutProperPermissions($entity_type, $serialized, $context);

    }

  }

  /**
   * Tests several valid and invalid create requests for 'node' entity type.
   */
  public function testCreateNode() {
    $entity_type = 'node';
    $this->enableService('entity:' . $entity_type, 'POST');
    // Create accounts (admin and non-admin).
    $accounts = $this->createAccountPerEntity($entity_type);

    foreach ($accounts as $key => $account) {
      $this->drupalLogin($account);
      $entity_values = $this->entityValues($entity_type);
      $entity = entity_create($entity_type, $entity_values);

      // Verify that user cannot create content when trying to write to fields where it is not possible.
      if (!$account->hasPermission('administer nodes')) {
        $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);
        $this->httpRequest('entity/' . $entity_type, 'POST', $serialized, $this->defaultMimeType);
        $this->assertResponse(403);
        // Remove fields where non-admin users cannot write.
        $entity = $this->removeNodeFieldsForNonAdminUsers($entity);
      }
      else {
        // Changed and revision_timestamp fields can never be added.
        unset($entity->changed);
        unset($entity->revision_timestamp);
      }

      $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);

      $this->createEntityOverRestApi($entity_type, $serialized);

      $this->readEntityIdFromHeaderAndDb($entity_type, $entity, $entity_values);

      $this->createEntityInvalidData($entity_type);

      $this->createEntityNoData($entity_type);

      $this->createEntityInvalidSerialized($entity, $entity_type);

      $this->createEntityWithoutProperPermissions($entity_type, $serialized);

    }

  }

  /**
   * Tests several valid and invalid create requests for 'user' entity type.
   */
  public function testCreateUser() {
    $entity_type = 'user';
    $this->enableService('entity:' . $entity_type, 'POST');
    // Create accounts (admin and non-admin).
    $accounts = $this->createAccountPerEntity($entity_type);

    foreach ($accounts as $key => $account) {
      $this->drupalLogin($account);
      $entity_values = $this->entityValues($entity_type);
      $entity = entity_create($entity_type, $entity_values);

      // Verify that only administrative users can create users.
      if (!$account->hasPermission('administer users')) {
        $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);
        $this->httpRequest('entity/' . $entity_type, 'POST', $serialized, $this->defaultMimeType);
        $this->assertResponse(403);
        continue;
      }

      // Changed field can never be added.
      unset($entity->changed);

      $serialized = $this->serializer->serialize($entity, $this->defaultFormat, ['account' => $account]);

      $this->createEntityOverRestApi($entity_type, $serialized);

      $this->readEntityIdFromHeaderAndDb($entity_type, $entity, $entity_values);

      $this->createEntityInvalidData($entity_type);

      $this->createEntityNoData($entity_type);

      $this->createEntityInvalidSerialized($entity, $entity_type);
    }

  }

  /**
   * Creates user accounts(admin and non-admin) that have the required permissions to create resources via the REST API.
   *
   * @param $entity_type
   * @return array
   */
  public function createAccountPerEntity($entity_type) {
    $accounts = array();
    $permissions = $this->entityPermissions($entity_type, 'create');
    $permissions[] = 'restful post entity:' . $entity_type;
    // Create non-admin user.
    $accounts[] = $this->drupalCreateUser($permissions);
    // Admin permissions.
    $permissions[] = 'administer nodes';
    $permissions[] = 'administer users';
    // Create admin user.
    $accounts[] = $this->drupalCreateUser($permissions);

    return $accounts;
  }

  /**
   * Create the entity over the REST API.
   *
   * @param $entity_type
   * @param $serialized
   */
  public function createEntityOverRestApi($entity_type, $serialized) {
    $this->httpRequest('entity/' . $entity_type, 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(201);
  }

  /**
   * Get the new entity ID from the location header and try to read it from the database.
   *
   * @param $entity_type
   * @param $entity
   * @param $entity_values
   */
  public function readEntityIdFromHeaderAndDb($entity_type, $entity, $entity_values) {
    $location_url = $this->drupalGetHeader('location');
    $url_parts = explode('/', $location_url);
    $id = end($url_parts);
    $loaded_entity = entity_load($entity_type, $id);
    $this->assertNotIdentical(FALSE, $loaded_entity, 'The new ' . $entity_type . ' was found in the database.');
    $this->assertEqual($entity->uuid(), $loaded_entity->uuid(), 'UUID of created entity is correct.');
    // @todo Remove the user reference field for now until deserialization for
    // entity references is implemented.
    unset($entity_values['user_id']);
    foreach ($entity_values as $property => $value) {
      $actual_value = $loaded_entity->get($property)->value;
      $send_value = $entity->get($property)->value;
      $this->assertEqual($send_value, $actual_value, 'Created property ' . $property . ' expected: ' . $send_value . ', actual: ' . $actual_value);
    }

    $loaded_entity->delete();
  }

  /**
   * Try to send invalid data that cannot be correctly deserialized.
   *
   * @param $entity_type
   */
  public function createEntityInvalidData($entity_type) {
    $this->httpRequest('entity/' . $entity_type, 'POST', 'kaboom!', $this->defaultMimeType);
    $this->assertResponse(400);
  }

  /**
   * Try to send no data at all, which does not make sense on POST requests.
   *
   * @param $entity_type
   */
  public function createEntityNoData($entity_type) {
    $this->httpRequest('entity/' . $entity_type, 'POST', NULL, $this->defaultMimeType);
    $this->assertResponse(400);
  }

  /**
   * Try to send invalid data to trigger the entity validation constraints. Send a UUID that is too long.
   *
   * @param $entity
   * @param $entity_type
   * @param array $context
   */
  public function createEntityInvalidSerialized($entity, $entity_type, $context = array()) {
    $entity->set('uuid', $this->randomMachineName(129));
    $invalid_serialized = $this->serializer->serialize($entity, $this->defaultFormat, $context);
    $response = $this->httpRequest('entity/' . $entity_type, 'POST', $invalid_serialized, $this->defaultMimeType);
    $this->assertResponse(422);
    $error = Json::decode($response);
    $this->assertEqual($error['error'], "Unprocessable Entity: validation failed.\nuuid.0.value: <em class=\"placeholder\">UUID</em>: may not be longer than 128 characters.\n");
  }

  /**
   * Try to create an entity without proper permissions.
   *
   * @param $entity_type
   * @param $serialized
   */
  public function createEntityWithoutProperPermissions($entity_type, $serialized) {
    $this->drupalLogout();
    $this->httpRequest('entity/' . $entity_type, 'POST', $serialized, $this->defaultMimeType);
    $this->assertResponse(403);
    $this->assertFalse(entity_load_multiple($entity_type, NULL, TRUE), 'No entity has been created in the database.');
  }

}
