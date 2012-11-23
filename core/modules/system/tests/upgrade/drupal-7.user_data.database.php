<?php

/**
 * @file
 * Database additions for role tests. Used in upgrade.roles.test.
 *
 * This dump only contains data and schema components relevant for role
 * functionality. The drupal-7.bare.database.php file is imported before
 * this dump, so the two form the database structure expected in tests
 * altogether.
 */

debug(db_query('SELECT * {users}')->fetchAll());