<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Config\Entity\ConfigEntityTypeTest.
 */

namespace Drupal\Tests\Core\Config\Entity;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\Entity\ConfigEntityType;

/**
 * @coversDefaultClass \Drupal\Core\Config\Entity\ConfigEntityType
 *
 * @group Drupal
 * @group Config
 */
class ConfigEntityTypeTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\Core\Config\Entity\ConfigEntityType unit test',
      'group' => 'Entity',
    );
  }

  /**
   * Tests that we get an exception when the config prefix is too long.
   *
   * @expectedException \Drupal\Core\Config\ConfigPrefixLengthException
   * @expectedExceptionMessage The extra_long_provider_name.long_random_configuration_prefix_so_that_go_over_the_limit12 config_prefix length is larger than the maximum limit of 83 characters
   * @covers ::getConfigPrefix()
   */
  public function testConfigPrefixLengthWithPrefixExceeds() {
    // A config entity with a provider length of 24 and config_prefix length of 60
    // (+1 for the .) results in a config length of 85, which is too long.
    $config_entity = new ConfigEntityType(array(
      'provider' => 'extra_long_provider_name',
      'config_prefix' => 'long_random_configuration_prefix_so_that_go_over_the_limit12',
    ));
    $this->assertEmpty($config_entity->getConfigPrefix());
  }

  /**
   * Tests that we get an exception when the id is too long.
   *
   * @expectedException \Drupal\Core\Config\ConfigPrefixLengthException
   * @expectedExceptionMessage The extra_long_provider_name.long_random_entity_id_so_that_we_will_go_over_the_limit12345 config_prefix length is larger than the maximum limit of 83 characters
   * @covers ::getConfigPrefix()
   */
  public function testConfigPrefixLengthWithIdExceeds() {
    // A config entity with an provider length of 24 and id length of 60
    // (+1 for the .) results in a config length of 85, which is too long.
    $config_entity = new ConfigEntityType(array(
      'provider' => 'extra_long_provider_name',
      'id' => 'long_random_entity_id_so_that_we_will_go_over_the_limit12345',
    ));
    $this->assertEmpty($config_entity->getConfigPrefix());
  }

  /**
   * Tests that a valid config prefix does not throw an exception.
   *
   * @covers ::getConfigPrefix()
   */
  public function testConfigPrefixLengthWithPrefixValid() {
    // A config entity with a provider length of 24 and config_prefix length of 58
    // (+1 for the .) results in a config length of 83, which is right at the limit.
    $entity_data = array(
      'provider' => $this->randomName(24),
      'config_prefix' => $this->randomName(58),
    );
    $config_entity = new ConfigEntityType($entity_data);
    $expected_prefix = $entity_data['provider'] . '.' . $entity_data['config_prefix'];
    $this->assertEquals($expected_prefix, $config_entity->getConfigPrefix());
  }

  /**
   * Tests that a valid config prefix does not throw an exception.
   *
   * @covers ::getConfigPrefix()
   */
  public function testConfigPrefixLengthWithIdValid() {
    // A config entity with an provider length of 24 and id length of 58
    // (+1 for the .) results in a config length of 83, which is right at the limit.
    $entity_data = array(
      'provider' => $this->randomName(24),
      'id' => $this->randomName(58),
    );
    $config_entity = new ConfigEntityType($entity_data);
    $expected_prefix = $entity_data['provider'] . '.' . $entity_data['id'];
    $this->assertEquals($expected_prefix, $config_entity->getConfigPrefix());
  }

}
