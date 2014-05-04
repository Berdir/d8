<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Tests\EntityReferenceLanguageSupportTest.
 */

namespace Drupal\entity_reference\Tests;

use Drupal\content_translation\Tests\ContentTranslationTestBase;
use Drupal\system\Tests\Entity;
use Drupal\Tests\Core\Utility;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Field;

/**
 * Tests that the entity label is displayed in the correct language.
 */
class EntityReferenceLanguageSupportTest extends ContentTranslationTestBase {

  /**
   * The name of the field used in this test.
   *
   * @var string
   */
  protected $fieldName = 'field_test';

  /**
   * A field instance.
   *
   * @var \Drupal\field\FieldInstanceConfigInterface
   */
  protected $instance;

  /**
   * The name of the entity we using for the current test.
   *
   * @var string
   */
  protected $entityType = 'entity_test_mul';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('language', 'content_translation', 'entity_test', 'entity_reference');

  public static function getInfo() {
    return array(
      'name' => 'Entity reference language support',
      'description' => 'Verifying that the query is performed using the correct language.',
      'group' => 'Entity Reference',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditorPermissions() {
      return array('administer entity_test content');
  }

  /**
   * Creates the test fields.
   */
  protected function setupTestFields() {
    // Create field the field and his instance.
    $field = array(
      'name' => $this->fieldName,
      'type' => 'entity_reference',
      'entity_type' => $this->entityType,
      'cardinality' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => array(
        'target_type' => $this->entityType,
      ),
    );
    entity_create('field_config', $field)->save();

    $instance = array(
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityType,
      'bundle' => $this->bundle,
      'label' => 'Entity reference',
      'settings' => array(
        'handler' => 'default',
        'handler_settings' => array(),
      ),
    );
    entity_create('field_instance_config', $instance)->save();
    entity_get_form_display($this->entityType, $this->bundle, 'default')
      ->setComponent($this->fieldName, array(
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->save();

    $this->instance = Field::fieldInfo()->getInstance($this->entityType, $this->bundle, $this->fieldName);
  }

  /**
   * Tests the the language of the label in different languages.
   */
  function testEntityLabelLanguage() {
    $entity1_names = array(
      'en' => 'default',
      'fr' => 'french',
      'it' => 'italian',
    );


    $values = array(
      'name' => $entity1_names['en'],
      'type' => 'entity_test_mul',
    );

    $entity1 = entity_create('entity_test_mul', $values);
    $entity1->addTranslation('fr', array('name' => $entity1_names['fr']));
    $entity1->addTranslation('it', array('name' => $entity1_names['it']));
    $entity1->save();

    // Get values from selection handler.
    $handler = \Drupal::service('plugin.manager.entity_reference.selection')->getSelectionHandler($this->instance);

    // Test results for Italian.
    $expected_results = array (
      'entity_test_mul' => array (
        1 => 'italian',
      ),
    );
    $this->assertEqual($handler->getReferenceableEntities(NULL, 'CONTAINS', 0, 'it'), $expected_results);

    // Test results for French.
    $expected_results = array (
      'entity_test_mul' => array (
        1 => 'french',
      ),
    );
    $this->assertEqual($handler->getReferenceableEntities(NULL, 'CONTAINS', 0, 'fr'), $expected_results);

    // Test entity reference display in edit form.
    $entity2_names = array(
      'en' => 'referrer default',
      'fr' => 'referrer fr',
      'it' => 'referrer it',
    );

    $values = array(
      'name' => $entity2_names['en'],
      'type' => 'entity_test_mul',
    );

    $field_name = $this->fieldName;

    // Create new entity and reference entity1.
    $entity2 = entity_create('entity_test_mul', $values);
    $entity2->{$field_name}->entity = $entity1;
    $entity2->addTranslation('fr', array('name' => $entity2_names['fr']));
    $entity2->addTranslation('it', array('name' => $entity2_names['it']));
    $entity2->save();

    $this->drupalLogin($this->editor);

    $languages = \Drupal::languageManager()->getLanguages();

    foreach ($languages as $language) {
      $edit_path = $entity2->getSystemPath('edit-form');
      $options = array('language' => $language);
      $this->drupalGet($edit_path, $options);
      $this->assertFieldByName("{$field_name}[0][target_id]", $entity1_names[$language->id] . ' (' . $entity1->id() . ')', 'Widget is displayed with the correct default value');
    }
  }

}
