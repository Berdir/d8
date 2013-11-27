<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Tests\process;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\migrate\Plugin\migrate\process\DedupeEntity;

class DedupeEntityTest extends MigrateProcessTestCase {

  /**
   * The mock entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Dedupe entity process plugin',
      'description' => 'Tests the entity deduplication process plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityQuery = $this->getMockBuilder('Drupal\Core\Entity\Query\QueryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    parent::setUp();
  }

  /**
   * Test the entity deduplication plugin when there is no duplication.
   */
  public function testDedupeEntityNoDuplication() {
    $configuration = array(
      'entity_type' => 'test_entity_type',
      'field' => 'test_field',
    );
    $plugin = new TestDedupeEntity($configuration, 'dedupe_entity', array());
    $this->entityQueryExpects(0);
    $plugin->setEntityQuery($this->entityQuery);
    $return = $plugin->transform('test', $this->migrateExecutable, $this->row, 'testpropertty');
    $this->assertSame($return, 'test');
  }

  /**
   * Test the entity deduplication plugin when there is duplication.
   */
  public function testDedupeEntityDuplication() {
    $configuration = array(
      'entity_type' => 'test_entity_type',
      'field' => 'test_field',
    );
    $plugin = new TestDedupeEntity($configuration, 'dedupe_entity', array());
    $this->entityQueryExpects(3);
    $plugin->setEntityQuery($this->entityQuery);
    $return = $plugin->transform('test', $this->migrateExecutable, $this->row, 'testpropertty');
    $this->assertSame($return, 'test3');
  }

  /**
   * Test the entity deduplication plugin when there is no duplication.
   */
  public function testDedupeEntityNoDuplicationWithPostfix() {
    $configuration = array(
      'entity_type' => 'test_entity_type',
      'field' => 'test_field',
      'postfix' => '_',
    );
    $plugin = new TestDedupeEntity($configuration, 'dedupe_entity', array());
    $this->entityQueryExpects(0);
    $plugin->setEntityQuery($this->entityQuery);
    $return = $plugin->transform('test', $this->migrateExecutable, $this->row, 'testpropertty');
    $this->assertSame($return, 'test');
  }

  /**
   * Test the entity deduplication plugin when there is duplication.
   */
  public function testDedupeEntityDuplicationWithPostfix() {
    $configuration = array(
      'entity_type' => 'test_entity_type',
      'field' => 'test_field',
      'postfix' => '_',
    );
    $plugin = new TestDedupeEntity($configuration, 'dedupe_entity', array());
    $this->entityQueryExpects(2);
    $plugin->setEntityQuery($this->entityQuery);
    $return = $plugin->transform('test', $this->migrateExecutable, $this->row, 'testpropertty');
    $this->assertSame($return, 'test_2');
  }

  /**
   * Helper adding expectations to the mock entity object.
   *
   * @param $count
   *   The number of deduplications to be set up.
   */
  protected function entityQueryExpects($count) {
    $this->entityQuery->expects($this->exactly($count + 1))
      ->method('condition')
      ->will($this->returnValue($this->entityQuery));
    $this->entityQuery->expects($this->exactly($count + 1))
      ->method('count')
      ->will($this->returnValue($this->entityQuery));
    $this->entityQuery->expects($this->exactly($count + 1))
      ->method('execute')
      ->will($this->returnCallback(function () use (&$count) { return $count--;}));
  }
}

class TestDedupeEntity extends DedupeEntity {
  function setEntityQuery(QueryInterface $entity_query) {
    $this->entityQuery = $entity_query;
  }
}
