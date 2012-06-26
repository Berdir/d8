<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Form\ElementsAccessTest.
 */

namespace Drupal\system\Tests\Form;

use Drupal\simpletest\WebTestBase;

/**
 * Tests access control for form elements.
 */
class ElementsAccessTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Form element access',
      'description' => 'Tests access control for form elements.',
      'group' => 'Form API',
    );
  }

  function setUp() {
    parent::setUp(array('form_test'));
  }

  /**
   * Ensures that child values are still processed when #access = FALSE.
   */
  function testAccessFalse() {
    $this->drupalPost('form_test/vertical-tabs-access', array(), t('Submit'));
    $this->assertNoText(t('This checkbox inside a vertical tab does not have its default value.'));
    $this->assertNoText(t('This textfield inside a vertical tab does not have its default value.'));
    $this->assertNoText(t('This checkbox inside a fieldset does not have its default value.'));
    $this->assertNoText(t('This checkbox inside a container does not have its default value.'));
    $this->assertNoText(t('This checkbox inside a nested container does not have its default value.'));
    $this->assertText(t('The form submitted correctly.'));
  }

}
