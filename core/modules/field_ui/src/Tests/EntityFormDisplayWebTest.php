<?php

/**
 * @file
 * Contains \Drupal\field_ui\Tests\EntityFormDisplayWebTest.
 */

namespace Drupal\field_ui\Tests;

use Drupal\entity_test\Entity\EntityTestBaseFieldDisplay;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity display configuration entities.
 *
 * @group field_ui
 */
class EntityFormDisplayWebTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var string[]
   */
  public static $modules = ['field_ui', 'field', 'entity_test', 'field_test', 'user', 'text'];

  /**
   * Tests the behavior of a field widget for a base field.
   */
  public function testBaseFieldComponent() {

    $user = $this->drupalCreateUser(['administer entity_test content']);
    $this->drupalLogin($user);

    $entity = EntityTestBaseFieldDisplay::create(['name' => $this->randomString()]);
    $entity->save();

    $this->drupalGet('entity_test_base_field_display/manage/' . $entity->id());
    $this->assertRaw($entity->label());
    $this->assertText('A field with multiple values');
    $this->drupalPostForm(NULL, [], t('Add another item'));

    $edit['test_display_multiple[0][value]'] = $this->randomString();
    $edit['test_display_multiple[1][value]'] = $this->randomString();
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('entity_test_base_field_display ' . $entity->id() . ' has been updated.');



  }

}
