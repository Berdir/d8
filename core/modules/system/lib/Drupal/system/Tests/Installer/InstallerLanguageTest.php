<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Installer\InstallerLanguageTest.
 */

namespace Drupal\system\Tests\Installer;

use Drupal\simpletest\WebTestBase;

/**
 * Tests installer language detection.
 */
class InstallerLanguageTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Installer language tests',
      'description' => 'Tests for installer language support.',
      'group' => 'Installer',
    );
  }

  function setUp() {
    parent::setUp();
    // The database is not available during this part of install. Use global
    // $conf to override the installation translations directory path.
    global $conf;
    $conf['locale.settings']['translation.path'] =  drupal_get_path('module', 'simpletest') . '/files/translations';
  }

  /**
   * Tests that the installer can find translation files.
   */
  function testInstallerTranslationFiles() {
    include_once DRUPAL_ROOT . '/core/includes/install.core.inc';

    // Different translation files would be found depending on which language
    // we are looking for.
    $expected_translation_files = array(
      NULL => array('drupal-8.0.hu.po', 'drupal-8.0.de.po'),
      'de' => array('drupal-8.0.de.po'),
      'hu' => array('drupal-8.0.hu.po'),
      'it' => array(),
    );

    foreach ($expected_translation_files as $langcode => $files_expected) {
      $files_found = install_find_translation_files($langcode);
      $this->assertTrue(count($files_found) == count($files_expected), format_string('@count installer languages found.', array('@count' => count($files_expected))));
      foreach ($files_found as $file) {
        $this->assertTrue(in_array($file->filename, $files_expected), format_string('@file found.', array('@file' => $file->filename)));
      }
    }
  }

}
