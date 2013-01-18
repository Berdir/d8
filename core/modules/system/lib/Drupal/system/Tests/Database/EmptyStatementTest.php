<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Database\EmptyStatementTest.
 */

namespace Drupal\system\Tests\Database;

use Drupal\Core\Database\StatementEmpty;
use Drupal\Core\Database\StatementInterface;
use Drupal\simpletest\UnitTestBase;

/**
 * Tests the empty pseudo-statement class.
 */
class EmptyStatementTest extends UnitTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Empty statement',
      'description' => 'Test the empty pseudo-statement class.',
      'group' => 'Database',
    );
  }

  /**
   * Tests that the empty result set behaves as empty.
   */
  function testEmpty() {
    $result = new StatementEmpty();

    $this->assertTrue($result instanceof StatementInterface, 'Class implements expected interface');
    $this->assertNull($result->fetchObject(), 'Null result returned.');
  }

  /**
   * Tests that the empty result set iterates safely.
   */
  function testEmptyIteration() {
    $result = new StatementEmpty();

    foreach ($result as $record) {
      $this->fail('Iterating empty result set should not iterate.');
      return;
    }

    $this->pass('Iterating empty result set skipped iteration.');
  }

  /**
   * Tests that the empty result set mass-fetches in an expected way.
   */
  function testEmptyFetchAll() {
    $result = new StatementEmpty();

    $this->assertEqual($result->fetchAll(), array(), 'Empty array returned from empty result set.');
  }
}
