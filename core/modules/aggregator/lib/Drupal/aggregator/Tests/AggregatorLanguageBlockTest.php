<?php

/**
 * @file
 * Definition of Drupal\aggregator\Tests\AggregatorLanguageBlockTest.
 */

namespace Drupal\aggregator\Tests;

use Drupal\Core\Language\Language;

/**
 * Tests rendering functionality in the Aggregator module.
 */
class AggregatorLanguageBlockTest extends AggregatorTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'language');

  /**
   * List of langcodes.
   *
   * @var array
   */
  protected $langcodes = array();

  public static function getInfo() {
    return array(
      'name' => 'Multilingual blocks',
      'description' => 'Checks display of aggregator blocks with multiple languages.',
      'group' => 'Aggregator'
    );
  }

  public function setUp() {
    parent::setUp();

    // Create test languages.
    $this->langcodes = array(language_load('en'));
    for ($i = 1; $i < 3; ++$i) {
      $language = new Language(array(
        'langcode' => 'l' . $i,
        'name' => $this->randomString(),
      ));
      language_save($language);
      $this->langcodes[$i] = $language;
    }
  }

  /**
   * Create a block in a language, check blocks page in all languages.
   */
  public function testBlockLinks() {
    // Need admin user to be able to access block admin.
    $admin_user = $this->drupalCreateUser(array(
      'administer blocks',
      'access administration pages',
      'administer news feeds',
      'access news feeds',
      'create article content',
      'administer languages',
    ));
    $this->drupalLogin($admin_user);

    // Save language prefixes.
    //$this->drupalPost('admin/config/regional/language/detection/url', array('prefix[en]' => 'en'), t('Save configuration'));

    // Create the block cache for all languages.
    foreach ($this->langcodes as $langcode) {
      $this->drupalGet('admin/structure/block', array('language' => $langcode));
      $this->clickLink(t('Add block'));
    }

    // Create a feed in the default language.
    $this->createSampleNodes();
    $feed = $this->createFeed();

    // Check that the block is listed for all languages.
    foreach ($this->langcodes as $langcode) {
      $this->drupalGet('admin/structure/block', array('language' => $langcode));
      $this->clickLink(t('Add block'));
      $this->assertText($feed->title);
    }
  }
}
