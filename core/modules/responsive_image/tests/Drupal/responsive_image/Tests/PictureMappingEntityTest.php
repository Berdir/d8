<?php

/**
 * @file
 * Contains \Drupal\responsive_image\Tests\ResponsiveImageMappingEntityTest.
 */

namespace Drupal\picture\Tests;

use Drupal\responsive_image\Entity\ResponsiveImageMapping;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\picture\Entity\PictureMapping
 *
 * @group Drupal
 * @group Config
 */
class ResponsiveImageMappingEntityTest extends UnitTestCase {

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
   * The ID of the breakpoint group used for testing.
   *
   * @var string
   */
  protected $breakpointGroupId;

  /**
   * The breakpoint group used for testing.
   *
   * @var \Drupal\breakpoint\Entity\BreakpointGroup|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $breakpointGroup;

  /**
   * The breakpoint group storage controller used for testing.
   *
   * @var \Drupal\Core\Config\Entity\ConfigStorageControllerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $breakpointGroupStorageController;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\responsive_image\Entity\ResponsiveImageMapping unit test',
      'group' => 'Picture',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
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

    $this->breakpointGroupId = $this->randomName(9);
    $this->breakpointGroup = $this->getMock('Drupal\breakpoint\Entity\BreakpointGroup', array(), array(array('id' => $this->breakpointGroupId)));

    $this->breakpointGroupStorageController = $this->getMock('\Drupal\Core\Config\Entity\ConfigStorageControllerInterface');
    $this->breakpointGroupStorageController
      ->expects($this->any())
      ->method('load')
      ->with($this->breakpointGroupId)
      ->will($this->returnValue($this->breakpointGroup));

    $this->entityManager->expects($this->any())
      ->method('getStorageController')
      ->will($this->returnValue($this->breakpointGroupStorageController));

    $container = new ContainerBuilder();
    $container->set('entity.manager', $this->entityManager);
    $container->set('uuid', $this->uuid);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $picture_mapping = new ResponsiveImageMapping(array(), $this->entityTypeId);
    // Set the breakpoint group after creating the entity to avoid the calls
    // in the constructor.
    $picture_mapping->breakpointGroup = $this->breakpointGroupId;
    $this->breakpointGroup->expects($this->once())
      ->method('getConfigDependencyName')
      ->will($this->returnValue('breakpoint.breakpoint_group.' . $this->breakpointGroupId));

    $dependencies = $picture_mapping->calculateDependencies();
    $this->assertContains('breakpoint.breakpoint_group.' . $this->breakpointGroupId, $dependencies['entity']);
  }

}
