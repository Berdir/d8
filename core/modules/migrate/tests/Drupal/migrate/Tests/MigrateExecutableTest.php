<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateExecutableTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests the migrate executable.
 *
 * @group Drupal
 * @group migrate
 *
 * @covers \Drupal\migrate\Tests\MigrateExecutableTest
 */
class MigrateExecutableTest extends MigrateTestCase {

  /**
   * The mocked migration entity.
   *
   * @var \Drupal\migrate\Entity\MigrationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $migration;

  /**
   * The mocked migrate message.
   *
   * @var \Drupal\migrate\MigrateMessageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $message;

  /**
   * The tested migrate executable.
   *
   * @var \Drupal\migrate\Tests\TestMigrateExecutable
   */
  protected $executable;

  protected $mapJoinable = FALSE;

  protected $migrationConfiguration = array(
    'id' => 'test',
    'limit' => array('unit' => 'second', 'value' => 1),
    'timeThreshold' => 0.9,
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate executable',
      'description' => 'Tests the migrate executable.',
      'group' => 'Migrate',
    );
  }

  protected function setUp() {
    $this->migration = $this->getMigration();
    $this->message = $this->getMock('Drupal\migrate\MigrateMessageInterface');
    $id_map = $this->getMock('Drupal\migrate\Plugin\MigrateIdMapInterface');

    $this->migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($id_map));

    $this->executable = new TestMigrateExecutable($this->migration, $this->message);
    $this->executable->setTranslationManager($this->getStringTranslationStub());
  }

  /**
   * Tests an import with an incomplete rewinding.
   */
  public function testImportWithFailingRewind() {
    $iterator = $this->getMock('\Iterator');
    $exception_message = $this->getRandomGenerator()->string();
    $iterator->expects($this->once())
      ->method('valid')
      ->will($this->returnCallback(function() use ($exception_message) {
        throw new \Exception($exception_message);
      }));
    $source = $this->getMock('Drupal\migrate\Plugin\MigrateSourceInterface');
    $source->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $this->migration->expects($this->any())
      ->method('getSourcePlugin')
      ->will($this->returnValue($source));

    // Ensure that a message with the proper message was added.
    $this->message->expects($this->once())
      ->method('display')
      ->with("Migration failed with source plugin exception: $exception_message");

    $result = $this->executable->import();
    $this->assertEquals(MigrationInterface::RESULT_FAILED, $result);
  }

  /**
   * Tests time limit option method.
   */
  public function testTimeOptionExceeded() {
    // Assert time limit of one second (test configuration default) is exceeded.
    $this->assertTrue($this->executable->timeOptionExceeded());
    // Assert time limit not exceeded.
    $this->migration->set('limit', array('unit' => 'seconds', 'value' => (REQUEST_TIME - 3600)));
    $this->assertFalse($this->executable->timeOptionExceeded());
    // Assert no time limit.
    $this->migration->set('limit', array());
    $this->assertFalse($this->executable->timeOptionExceeded());
  }

  /**
   * Tests get time limit method.
   */
  public function testGetTimeLimit() {
    // Assert time limit has a unit of one second (test configuration default).
    $limit = $this->migration->get('limit');
    $this->assertArrayHasKey('unit', $limit);
    $this->assertSame('second', $limit['unit']);
    $this->assertSame($limit['value'], $this->executable->getTimeLimit());
    // Assert time limit has a unit of multiple seconds.
    $this->migration->set('limit', array('unit' => 'seconds', 'value' => 30));
    $limit = $this->migration->get('limit');
    $this->assertArrayHasKey('unit', $limit);
    $this->assertSame('seconds', $limit['unit']);
    $this->assertSame($limit['value'], $this->executable->getTimeLimit());
    // Assert no time limit.
    $this->migration->set('limit', array());
    $limit = $this->migration->get('limit');
    $this->assertArrayNotHasKey('unit', $limit);
    $this->assertArrayNotHasKey('value', $limit);
    $this->assertNull($this->executable->getTimeLimit());
  }

  /**
   * Tests maximum execution time (max_execution_time) of an import.
   */
  public function testMaxExecTimeExceeded() {
    // Assert no max_execution_time value.
    $this->executable->setMaxExecTime(0);
    $this->assertFalse($this->executable->maxExecTimeExceeded());
    // Assert default max_execution_time value does not exceed.
    $this->executable->setMaxExecTime(30);
    $this->assertFalse($this->executable->maxExecTimeExceeded());
    // Assert max_execution_time value is exceeded.
    $this->executable->setMaxExecTime(1);
    // Sleep to force a higher execution time but not slow other tests.
    sleep(1);
    $this->assertTrue($this->executable->maxExecTimeExceeded());
  }

}

class TestMigrateExecutable extends MigrateExecutable {

  public function setTranslationManager(TranslationInterface $translation_manager) {
    $this->translationManager = $translation_manager;
  }

  /**
   * Allows access to protected timeOptionExceeded method.
   */
  public function timeOptionExceeded() {
    return parent::timeOptionExceeded();
  }

  /**
   * Allows access to set protected maxExecTime property.
   */
  public function setMaxExecTime($max_exec_time) {
    $this->maxExecTime = $max_exec_time;
  }

  /**
   * Allows access to protected maxExecTime property.
   */
  public function getMaxExecTime() {
    return $this->maxExecTime;
  }

  /**
   * Allows access to protected maxExecTimeExceeded method.
   */
  public function maxExecTimeExceeded() {
    return parent::maxExecTimeExceeded();
  }

}
