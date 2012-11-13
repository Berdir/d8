<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Database\SelectComplexTest.
 */

namespace Drupal\system\Tests\Database;

/**
 * Tests more complex select statements.
 */
class SelectWebTest extends DatabaseWebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node_access_test');

  public static function getInfo() {
    return array(
      'name' => 'Select tests, web tests',
      'description' => 'Select query builder tests that require a web test environment',
      'group' => 'Database',
    );
  }

  /**
   * Tests that we can join on a query.
   */
  function testJoinSubquery() {
    $acct = $this->drupalCreateUser();
    $this->drupalLogin($acct);

    $query = db_select('test_task', 'tt', array('target' => 'slave'));
    $query->addExpression('tt.pid + 1', 'abc');
    $query->condition('priority', 1, '>');
    $query->condition('priority', 100, '<');

    $subquery = db_select('test', 'tp');
    $subquery->join('test_one_blob', 'tpb', 'tp.id = tpb.id');
    $subquery->join('node', 'n', 'tp.id = n.nid');
    $subquery->addTag('node_access');
    $subquery->addMetaData('account', $acct);
    $subquery->addField('tp', 'id');
    $subquery->condition('age', 5, '>');
    $subquery->condition('age', 500, '<');

    $query->leftJoin($subquery, 'sq', 'tt.pid = sq.id');
    $query->join('test_one_blob', 'tb3', 'tt.pid = tb3.id');

    // Construct the query string.
    // This is the same sequence that SelectQuery::execute() goes through.
    $query->preExecute();
    $query->getArguments();
    $str = (string) $query;

    // Verify that the string only has one copy of condition placeholder 0.
    $pos = strpos($str, 'db_condition_placeholder_0', 0);
    $pos2 = strpos($str, 'db_condition_placeholder_0', $pos + 1);
    $this->assertFalse($pos2, 'Condition placeholder is not repeated.');
  }
}
