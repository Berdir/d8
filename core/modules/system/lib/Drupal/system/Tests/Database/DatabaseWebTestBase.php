<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Database\DatabaseWebTestBase.
 */

namespace Drupal\system\Tests\Database;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for databases.
 *
 * Because all database tests share the same test data, we can centralize that
 * here.
 */
abstract class DatabaseWebTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('database_test');

  function setUp() {
    parent::setUp();

    $this->addSampleData();
  }

  /**
   * Sets up our sample data.
   *
   * These are added using db_query(), since we're not trying to test the
   * INSERT operations here, just populate.
   */
  function addSampleData() {
    // We need the IDs, so we can't use a multi-insert here.
    $john = db_insert('test')
      ->fields(array(
        'name' => 'John',
        'age' => 25,
        'job' => 'Singer',
      ))
      ->execute();

    $george = db_insert('test')
      ->fields(array(
        'name' => 'George',
        'age' => 27,
        'job' => 'Singer',
      ))
      ->execute();

    $ringo = db_insert('test')
      ->fields(array(
        'name' => 'Ringo',
        'age' => 28,
        'job' => 'Drummer',
      ))
      ->execute();

    $paul = db_insert('test')
      ->fields(array(
        'name' => 'Paul',
        'age' => 26,
        'job' => 'Songwriter',
      ))
      ->execute();

    db_insert('test_people')
      ->fields(array(
        'name' => 'Meredith',
        'age' => 30,
        'job' => 'Speaker',
      ))
      ->execute();

    db_insert('test_task')
      ->fields(array('pid', 'task', 'priority'))
      ->values(array(
        'pid' => $john,
        'task' => 'eat',
        'priority' => 3,
      ))
      ->values(array(
        'pid' => $john,
        'task' => 'sleep',
        'priority' => 4,
      ))
      ->values(array(
        'pid' => $john,
        'task' => 'code',
        'priority' => 1,
      ))
      ->values(array(
        'pid' => $george,
        'task' => 'sing',
        'priority' => 2,
      ))
      ->values(array(
        'pid' => $george,
        'task' => 'sleep',
        'priority' => 2,
      ))
      ->values(array(
        'pid' => $paul,
        'task' => 'found new band',
        'priority' => 1,
      ))
      ->values(array(
        'pid' => $paul,
        'task' => 'perform at superbowl',
        'priority' => 3,
      ))
      ->execute();
  }
}
