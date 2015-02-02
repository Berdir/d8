<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Config\Entity\Fixtures\ConfigEntityBaseWithThirdPartySettings.
 */

namespace Drupal\Tests\Core\Config\Entity\Fixtures;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;

/**
 * Enables testing of dependency calculation.
 *
 * @see \Drupal\Tests\Core\Config\Entity\ConfigEntityBaseUnitTest::testCalculateDependenciesWithThirdPartySettings()
 * @see \Drupal\Core\Config\Entity\ConfigEntityBase::calculateDependencies()
 */
abstract class ConfigEntityBaseWithThirdPartySettings extends ConfigEntityBase implements ThirdPartySettingsInterface {

}
