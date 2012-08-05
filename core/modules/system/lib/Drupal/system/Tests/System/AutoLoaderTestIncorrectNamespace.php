<?php

/**
 * @file
 * Definition of Drupal\system\Tests\System\AutoLoaderTestIncorrectNamespace.
 *
 * This is not a test; rather, it is simply a class which is deliberately
 * defined in the incorrect namespace for the PSR-0 autoloader to be able to
 * find it.
 *
 * See Drupal\system\Tests\System\AutoLoaderTest and autoloader_test_page(),
 * where this is used in an actual test.
 */

namespace Drupal\system\Tests\System\Autoloader;
class AutoLoaderTestIncorrectNamespace {}
