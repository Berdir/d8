<?php

/**
 * @file Contains \Drupal\Tests\Core\Annotation\ContextDefinitionTest.
 */

namespace Drupal\Tests\Core\Annotation;

use Drupal\Core\Annotation\ContextDefinition;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Annotation\ContextDefinition
 * @group Annotation
 */
class ContextDefinitionTest extends UnitTestCase {

  /**
   * The translation manager used for testing.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->translationManager = $this->getStringTranslationStub();
  }

  /**
   * Test the ContextDefinition Annotation.
   */
  public function testContextDefinitionAnnotation() {
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->translationManager);
    \Drupal::setContainer($container);

    $values = [
      'value' => 'entity:node',
      //'label' => new Translation(['value' => 'test']),
      'label' => 'test',
      'description' => 'test',
    ];
    $context_definition = new ContextDefinition($values);
    $definition = $context_definition->get();
    //throw new \Exception(print_r(, TRUE));
    // If the label or description are not a Translation annotation instance,
    // it should be replaced by NULL.
    $this->assertNull($definition->getLabel());
    $this->assertNull($definition->getDescription());

    $values = [
      'value' => 'entity:node',
      'label' => new Translation(['value' => 'test']),
      'description' => new Translation(['value' => 'test']),
    ];
    $context_definition = new ContextDefinition($values);
    $definition = $context_definition->get();
    //throw new \Exception(print_r(, TRUE));
    // If the label or description are a Translation annotation instance, the
    // translated string should be returned.
    $this->assertEquals('test', $definition->getLabel());
    $this->assertEquals('test', $definition->getDescription());
  }

}
