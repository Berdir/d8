<?php

/**
 * @file
 * Contains \Drupal\block\Tests\BlockSystemBrandingTest.
 */

namespace Drupal\block\Tests;

use Drupal\block\Entity\Block;

/**
 * Tests branding block display.
 *
 * @group block
 */
class BlockSystemBrandingTest extends BlockTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('block', 'system');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Set a site slogan.
    $this->config('system.site')
      ->set('slogan', 'Community plumbing')
      ->save();
    // Add the system branding block to the page.
    $this->drupalPlaceBlock('system_branding_block', array('region' => 'header', 'id' => 'site-branding'));
  }

  /**
   * Tests system branding block configuration.
   */
  public function testSystemBrandingSettings() {
    $site_logo_xpath = '//div[@id="block-site-branding"]//a[@class="site-logo"]';
    $site_name_xpath = '//div[@id="block-site-branding"]//div[@class="site-name"]';
    $site_slogan_xpath = '//div[@id="block-site-branding"]//div[@class="site-slogan"]';

    // Set default block settings.
    $this->drupalGet('');
    $site_logo_element = $this->xpath($site_logo_xpath);
    $site_name_element = $this->xpath($site_name_xpath);
    $site_slogan_element = $this->xpath($site_slogan_xpath);
    // Test that all branding elements are displayed.
    $this->assertTrue(!empty($site_logo_element), 'The branding block logo was found.');
    $this->assertTrue(!empty($site_name_element), 'The branding block site name was found.');
    $this->assertTrue(!empty($site_slogan_element), 'The branding block slogan was found.');
    $this->assertCacheTag('config:system.site');

    // Turn just the logo off.
    /** @var \Drupal\block\BlockInterface $block */
    $block = Block::load('site-branding');
    $block->getPlugin()->setConfigurationValue('use_site_logo', FALSE);
    $block->save();

    $this->drupalGet('');
    $site_logo_element = $this->xpath($site_logo_xpath);
    $site_name_element = $this->xpath($site_name_xpath);
    $site_slogan_element = $this->xpath($site_slogan_xpath);
    // Re-test all branding elements.
    $this->assertTrue(empty($site_logo_element), 'The branding block logo was disabled.');
    $this->assertTrue(!empty($site_name_element), 'The branding block site name was found.');
    $this->assertTrue(!empty($site_slogan_element), 'The branding block slogan was found.');
    $this->assertCacheTag('config:system.site');

    // Turn just the site name off.
    $block->getPlugin()
      ->setConfigurationValue('use_site_logo', TRUE)
      ->setConfigurationValue('use_site_name', FALSE);
    $block->save();

    $this->drupalGet('');
    $site_logo_element = $this->xpath($site_logo_xpath);
    $site_name_element = $this->xpath($site_name_xpath);
    $site_slogan_element = $this->xpath($site_slogan_xpath);
    // Re-test all branding elements.
    $this->assertTrue(!empty($site_logo_element), 'The branding block logo was found.');
    $this->assertTrue(empty($site_name_element), 'The branding block site name was disabled.');
    $this->assertTrue(!empty($site_slogan_element), 'The branding block slogan was found.');
    $this->assertCacheTag('config:system.site');

    // Turn just the site slogan off.
    $block->getPlugin()
      ->setConfigurationValue('use_site_name', TRUE)
      ->setConfigurationValue('use_site_slogan', FALSE);
    $block->save();

    $this->drupalGet('');
    $site_logo_element = $this->xpath($site_logo_xpath);
    $site_name_element = $this->xpath($site_name_xpath);
    $site_slogan_element = $this->xpath($site_slogan_xpath);
    // Re-test all branding elements.
    $this->assertTrue(!empty($site_logo_element), 'The branding block logo was found.');
    $this->assertTrue(!empty($site_name_element), 'The branding block site name was found.');
    $this->assertTrue(empty($site_slogan_element), 'The branding block slogan was disabled.');
    $this->assertCacheTag('config:system.site');

    // Turn the site name and the site slogan off.
    $block->getPlugin()
      ->setConfigurationValue('use_site_name', FALSE)
      ->setConfigurationValue('use_site_slogan', FALSE);
    $block->save();

    $this->drupalGet('');
    $site_logo_element = $this->xpath($site_logo_xpath);
    $site_name_element = $this->xpath($site_name_xpath);
    $site_slogan_element = $this->xpath($site_slogan_xpath);
    // Re-test all branding elements.
    $this->assertTrue(!empty($site_logo_element), 'The branding block logo was found.');
    $this->assertTrue(empty($site_name_element), 'The branding block site name was disabled.');
    $this->assertTrue(empty($site_slogan_element), 'The branding block slogan was disabled.');
    $this->assertCacheTag('config:system.site');
  }

}
