<?php

/**
 * @file
 * Contains \Drupal\test_page_test\Controller\TestPageTestController.
 */

namespace Drupal\test_page_test\Controller;

/**
 * Controller routines for test_page_test routes.
 */
class TestPageTestController {

  /**
   * @todo Remove test_page_test_page().
   */
  public function testPage() {
    $attached['js'][] = array(
      'data' => array('test-setting' => 'azAZ09();.,\\\/-_{}'),
      'type' => 'setting',
    );
    return array(
      '#title' => t('Test page'),
      '#markup' => t('Test page text.'),
      '#attached' => $attached,
    );
  }

}
