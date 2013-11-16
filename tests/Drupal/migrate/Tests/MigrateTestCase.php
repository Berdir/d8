<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateTestCase.
 */

namespace Drupal\migrate\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Provides setup and helper methods for Migrate module tests.
 */
abstract class MigrateTestCase extends UnitTestCase {

  /**
   * @TODO: does this need to be derived from the source/destination plugin?
   *
   * @var bool
   */
  protected $mapJoinable = TRUE;

  protected $migrationConfiguration = array();

  protected $expectedResults = array();

  /**
   * Retrieve a mocked migration.
   *
   * @return \Drupal\migrate\Entity\MigrationInterface
   *   The mocked migration.
   */
  protected function getMigration() {
    $idmap = $this->getMock('Drupal\migrate\Plugin\MigrateIdMapInterface');
    if ($this->mapJoinable) {
      $idmap->expects($this->once())
        ->method('getQualifiedMapTable')
        ->will($this->returnValue('test_map'));
    }

    $migration = $this->getMock('Drupal\migrate\Entity\MigrationInterface');
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($idmap));
    $configuration = $this->migrationConfiguration;
    $migration->expects($this->any())->method('get')->will($this->returnCallback(function ($argument) use ($configuration) {
      return isset($configuration[$argument]) ? $configuration[$argument] : '';
    }));
    $migration->expects($this->any())
      ->method('id')
      ->will($this->returnValue($configuration['id']));
    return $migration;
  }

  /**
   * Provide meta information about this battery of tests.
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate test',
      'description' => 'Tests for migrate plugin.',
      'group' => 'Migrate',
    );
  }


  /**
   * Tests a query
   *
   * @param array|\Traversable
   *   The countable. foreach-able actual results if a query is being run.
   */
  public function queryResultTest($iter) {
    $this->assertSame(count($this->expectedResults), count($iter), 'Number of results match');
    $count = 0;
    foreach ($iter as $data_row) {
      $expected_row = $this->expectedResults[$count];
      $count++;
      foreach ($expected_row as $key => $expected_value) {
        $this->retrievalAssertHelper($expected_value, $this->getValue($data_row, $key), sprintf('Value matches for key "%s"', $key));
      }
    }
    $this->assertSame(count($this->expectedResults), $count);
  }

  /**
   * @param array $row
   * @param string $key
   * @return mixed
   */
  protected function getValue($row, $key) {
    return $row[$key];
  }

  /**
   * Asserts tested values during test retrieval.
   *
   * @param mixed $expected_value
   *   The incoming expected value to test.
   * @param mixed $actual_value
   *   The incoming value itself.
   * @param string $message
   *   The tested result as a formatted string.
   */
  protected function retrievalAssertHelper($expected_value, $actual_value, $message) {
    if (is_array($expected_value)) {
      foreach ($expected_value as $k => $v) {
        $this->retrievalAssertHelper($v, $actual_value[$k], $message . '['. $k . ']');
      }
    }
    else {
      $this->assertSame((string) $expected_value, (string) $actual_value, $message);
    }
  }

}
