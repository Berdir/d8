<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Config\Entity\ConfigEntityBaseUnitTest.
 */

namespace Drupal\Tests\Core\Config\Entity;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Language\Language;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Config\Entity\ConfigEntityBase
 *
 * @group Drupal
 * @group Config
 */
class ConfigEntityBaseUnitTest extends UnitTestCase {

  /**
   * The entity under test.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entity;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The ID of the type of the entity under test.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The UUID generator used for testing.
   *
   * @var \Drupal\Component\Uuid\UuidInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $uuid;

  /**
   * The provider of the entity type.
   *
   * @var string
   */
  protected $provider;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\Core\Config\Entity\ConfigEntityBase unit test',
      'group' => 'Entity',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->id = $this->randomName();
    $values = array(
      'id' => $this->id,
      'langcode' => 'en',
      'uuid' => '3bb9ee60-bea5-4622-b89b-a63319d10b3a',
    );
    $this->entityTypeId = $this->randomName();
    $this->provider = $this->randomName();
    $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityType->expects($this->any())
      ->method('getProvider')
      ->will($this->returnValue($this->provider));

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->will($this->returnValue($this->entityType));

    $this->uuid = $this->getMock('\Drupal\Component\Uuid\UuidInterface');

    $this->languageManager = $this->getMock('\Drupal\Core\Language\LanguageManagerInterface');
    $this->languageManager->expects($this->any())
      ->method('getLanguage')
      ->with('en')
      ->will($this->returnValue(new Language(array('id' => 'en'))));

    $container = new ContainerBuilder();
    $container->set('entity.manager', $this->entityManager);
    $container->set('uuid', $this->uuid);
    $container->set('language_manager', $this->languageManager);
    \Drupal::setContainer($container);

    $this->entity = $this->getMockForAbstractClass('\Drupal\Core\Config\Entity\ConfigEntityBase', array($values, $this->entityTypeId));
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    // Calculating dependencies will reset the dependencies array.
    $this->entity->set('dependencies', array('module' => array('node')));
    $this->assertEmpty($this->entity->calculateDependencies());
  }

  /**
   * @covers ::preSave
   */
  public function testPreSaveDuringSync() {
    $query = $this->getMock('\Drupal\Core\Entity\Query\QueryInterface');
    $storage = $this->getMock('\Drupal\Core\Config\Entity\ConfigStorageControllerInterface');

    $query->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(array()));
    $query->expects($this->any())
      ->method('condition')
      ->will($this->returnValue($query));
    $storage->expects($this->any())
      ->method('getQuery')
      ->will($this->returnValue($query));
    $storage->expects($this->any())
      ->method('loadUnchanged')
      ->will($this->returnValue($this->entity));

    // Saving an entity will not reset the dependencies array during config
    // synchronization.
    $this->entity->set('dependencies', array('module' => array('node')));
    $this->entity->preSave($storage);
    $this->assertEmpty($this->entity->get('dependencies'));

    $this->entity->setSyncing(TRUE);
    $this->entity->set('dependencies', array('module' => array('node')));
    $this->entity->preSave($storage);
    $dependencies = $this->entity->get('dependencies');
    $this->assertContains('node', $dependencies['module']);
  }

  /**
   * @covers ::addDependency
   */
  public function testAddDependency() {
    $method = new \ReflectionMethod('\Drupal\Core\Config\Entity\ConfigEntityBase', 'addDependency');
    $method->setAccessible(TRUE);
    $method->invoke($this->entity, 'module', $this->provider);
    $method->invoke($this->entity, 'module', 'Core');
    $method->invoke($this->entity, 'module', 'node');
    $dependencies = $this->entity->get('dependencies');
    $this->assertNotContains($this->provider, $dependencies['module']);
    $this->assertNotContains('Core', $dependencies['module']);
    $this->assertContains('node', $dependencies['module']);

    // Test sorting of dependencies.
    $method->invoke($this->entity, 'module', 'action');
    $dependencies = $this->entity->get('dependencies');
    $this->assertEquals(array('action', 'node'), $dependencies['module']);

    // Test sorting of dependency types.
    $method->invoke($this->entity, 'entity', 'system.action.id');
    $dependencies = $this->entity->get('dependencies');
    $this->assertEquals(array('entity', 'module'), array_keys($dependencies));
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependenciesWithPluginBag() {
    $values = array();
    $this->entity = $this->getMockBuilder('\Drupal\Tests\Core\Config\Entity\Fixtures\ConfigEntityBaseWithPluginBag')
      ->setConstructorArgs(array($values, $this->entityTypeId))
      ->setMethods(array('getPluginBag'))
      ->getMock();

    // Create a configurable plugin that would add a dependency.
    $instance_id = $this->randomName();
    $instance = new TestConfigurablePlugin(array(), $instance_id, array('provider' => 'test'));

    // Create a plugin bag to contain the instance.
    $pluginBag = $this->getMockBuilder('\Drupal\Core\Plugin\DefaultPluginBag')
      ->disableOriginalConstructor()
      ->setMethods(array('get'))
      ->getMock();
    $pluginBag->expects($this->atLeastOnce())
      ->method('get')
      ->with($instance_id)
      ->will($this->returnValue($instance));
    $pluginBag->addInstanceId($instance_id);

    // Return the mocked plugin bag.
    $this->entity->expects($this->once())
                 ->method('getPluginBag')
                 ->will($this->returnValue($pluginBag));

    $dependencies = $this->entity->calculateDependencies();
    $this->assertContains('test', $dependencies['module']);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependenciesWithPluginBagSameProviderAsEntityType() {
    $values = array();
    $this->entity = $this->getMockBuilder('\Drupal\Tests\Core\Config\Entity\Fixtures\ConfigEntityBaseWithPluginBag')
                         ->setConstructorArgs(array($values, $this->entityTypeId))
                         ->setMethods(array('getPluginBag'))
                         ->getMock();

    // Create a configurable plugin that will not add a dependency since it is
    // provider matches the provider of the entity type.
    $instance_id = $this->randomName();
    $instance = new TestConfigurablePlugin(array(), $instance_id, array('provider' => $this->provider));

    // Create a plugin bag to contain the instance.
    $pluginBag = $this->getMockBuilder('\Drupal\Core\Plugin\DefaultPluginBag')
                      ->disableOriginalConstructor()
                      ->setMethods(array('get'))
                      ->getMock();
    $pluginBag->expects($this->atLeastOnce())
              ->method('get')
              ->with($instance_id)
              ->will($this->returnValue($instance));
    $pluginBag->addInstanceId($instance_id);

    // Return the mocked plugin bag.
    $this->entity->expects($this->once())
                 ->method('getPluginBag')
                 ->will($this->returnValue($pluginBag));

    $this->assertEmpty($this->entity->calculateDependencies());
  }

  /**
   * @covers ::setOriginalId
   * @covers ::getOriginalId
   */
  public function testGetOriginalId() {
    $new_id = $this->randomName();
    $this->entity->set('id', $new_id);
    $this->assertSame($this->id, $this->entity->getOriginalId());
    $this->assertSame($this->entity, $this->entity->setOriginalId($new_id));
    $this->assertSame($new_id, $this->entity->getOriginalId());
  }

  /**
   * @covers ::isNew
   */
  public function testIsNew() {
    $this->assertFalse($this->entity->isNew());
    $this->assertSame($this->entity, $this->entity->enforceIsNew());
    $this->assertTrue($this->entity->isNew());
    $this->entity->enforceIsNew(FALSE);
    $this->assertFalse($this->entity->isNew());
  }

  /**
   * @covers ::set
   * @covers ::get
   */
  public function testGet() {
    $name = 'id';
    $value = $this->randomName();
    $this->assertSame($this->id, $this->entity->get($name));
    $this->assertSame($this->entity, $this->entity->set($name, $value));
    $this->assertSame($value, $this->entity->get($name));
  }

  /**
   * @covers ::setStatus
   * @covers ::status
   */
  public function testSetStatus() {
    $this->assertTrue($this->entity->status());
    $this->assertSame($this->entity, $this->entity->setStatus(FALSE));
    $this->assertFalse($this->entity->status());
    $this->entity->setStatus(TRUE);
    $this->assertTrue($this->entity->status());
  }

  /**
   * @covers ::enable
   * @depends testSetStatus
   */
  public function testEnable() {
    $this->entity->setStatus(FALSE);
    $this->assertSame($this->entity, $this->entity->enable());
    $this->assertTrue($this->entity->status());
  }

  /**
   * @covers ::disable
   * @depends testSetStatus
   */
  public function testDisable() {
    $this->entity->setStatus(TRUE);
    $this->assertSame($this->entity, $this->entity->disable());
    $this->assertFalse($this->entity->status());
  }

  /**
   * @covers ::setSyncing
   * @covers ::isSyncing
   */
  public function testIsSyncing() {
    $this->assertFalse($this->entity->isSyncing());
    $this->assertSame($this->entity, $this->entity->setSyncing(TRUE));
    $this->assertTrue($this->entity->isSyncing());
    $this->entity->setSyncing(FALSE);
    $this->assertFalse($this->entity->isSyncing());
  }

  /**
   * @covers ::createDuplicate
   */
  public function testCreateDuplicate() {
    $this->entityType->expects($this->at(0))
      ->method('getKey')
      ->with('id')
      ->will($this->returnValue('id'));

    $this->entityType->expects($this->at(1))
      ->method('hasKey')
      ->with('uuid')
      ->will($this->returnValue(TRUE));

    $this->entityType->expects($this->at(2))
      ->method('getKey')
      ->with('uuid')
      ->will($this->returnValue('uuid'));

    $new_uuid = '8607ef21-42bc-4913-978f-8c06207b0395';
    $this->uuid->expects($this->once())
      ->method('generate')
      ->will($this->returnValue($new_uuid));

    $duplicate = $this->entity->createDuplicate();
    $this->assertInstanceOf('\Drupal\Core\Entity\Entity', $duplicate);
    $this->assertNotSame($this->entity, $duplicate);
    $this->assertNull($duplicate->id());
    $this->assertNull($duplicate->getOriginalId());
    $this->assertNotEquals($this->entity->uuid(), $duplicate->uuid());
    $this->assertSame($new_uuid, $duplicate->uuid());
  }

  /**
   * @covers ::sort
   */
  public function testSort() {
    $this->entityManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->will($this->returnValue(array(
        'entity_keys' => array(
          'label' => 'label',
        ),
      )));
    $entity_a = $this->entity;
    $entity_a->label = 'foo';
    $entity_b = clone $this->entity;
    $entity_b->label = 'bar';
    $list = array($entity_a, $entity_b);
    // Suppress errors because of https://bugs.php.net/bug.php?id=50688.
    @usort($list, '\Drupal\Core\Config\Entity\ConfigEntityBase::sort');
    $this->assertSame($entity_b, $list[0]);
    $entity_a->weight = 0;
    $entity_b->weight = 1;
    // Suppress errors because of https://bugs.php.net/bug.php?id=50688.
    @usort($list, array($entity_a, 'sort'));
    $this->assertSame($entity_a, $list[0]);
  }

  /**
   * @covers ::toArray
   */
  public function testToArray() {
    $properties = $this->entity->toArray();
    $this->assertInternalType('array', $properties);
    $class_info = new \ReflectionClass($this->entity);
    foreach ($class_info->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
      $name = $property->getName();
      $this->assertArrayHasKey($name, $properties);
      $this->assertSame($this->entity->get($name), $properties[$name]);
    }
  }
}

class TestConfigurablePlugin extends PluginBase implements ConfigurablePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }
}
