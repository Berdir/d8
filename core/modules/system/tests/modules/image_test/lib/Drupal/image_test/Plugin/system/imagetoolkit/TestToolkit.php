<?php

/**
 * @file
 * Contains \Drupal\image_test\Plugin\system\imagetoolkit\TestToolkit.
 */

namespace Drupal\image_test\Plugin\system\imagetoolkit;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\system\Plugin\ImageToolkitInterface;
use stdClass;

/**
 * Defines a Test toolkit for image manipulation within Drupal.
 *
 * @Plugin(
 *   id = "test",
 *   title = @Translation("A dummy toolkit that works")
 * )
 */
class TestToolkit extends PluginBase implements ImageToolkitInterface {

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::settingsForm().
   */
  public function settingsForm() {
    $this->logCall('settings', array());
    return array();
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::settingsFormSubmit().
   */
  public function settingsFormSubmit($form, &$form_state) {}

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::getInfo().
   */
  public function getInfo(stdClass $image) {
    $this->logCall('get_info', array($image));
    return array();
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::load().
   */
  public function load(stdClass $image) {
    $this->logCall('load', array($image));
    return $image;
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::save().
   */
  public function save(stdClass $image, $destination) {
    $this->logCall('save', array($image, $destination));
    // Return false so that image_save() doesn't try to chmod the destination
    // file that we didn't bother to create.
    return FALSE;
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::crop().
   */
  public function crop(stdClass $image, $x, $y, $width, $height) {
    $this->logCall('crop', array($image, $x, $y, $width, $height));
    return TRUE;
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::resize().
   */
  public function resize(stdClass $image, $width, $height) {
    $this->logCall('resize', array($image, $width, $height));
    return TRUE;
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::rotate().
   */
  public function rotate(stdClass $image, $degrees, $background = NULL) {
    $this->logCall('rotate', array($image, $degrees, $background));
    return TRUE;
  }

  /**
   * Implements \Drupal\system\Plugin\ImageToolkitInterface::desaturate().
   */
  public function desaturate(stdClass $image) {
    $this->logCall('desaturate', array($image));
    return TRUE;
  }

  /**
   * Stores the values passed to a toolkit call.
   *
   * @param string $op
   *   One of the image toolkit operations: 'get_info', 'load', 'save',
   *   'settings', 'resize', 'rotate', 'crop', 'desaturate'.
   * @param array $args
   *   Values passed to hook.
   *
   * @see \Drupal\system\Tests\Image\ToolkitTestBase::imageTestReset()
   * @see \Drupal\system\Tests\Image\ToolkitTestBase::imageTestGetAllCalls()
   */
  protected function logCall($op, $args) {
    $results = state()->get('image_test.results') ?: array();
    $results[$op][] = $args;
    state()->set('image_test.results', $results);
  }

  /**
   * Implements Drupal\system\Plugin\ImageToolkitInterface::isAvailable().
   */
  public static function isAvailable() {
    return TRUE;
  }
}
