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
   * Returns a test page and sets the title..
   */
  public function testPage() {
    $attached['drupalSettings']['test-setting'] = 'azAZ09();.,\\\/-_{}';
    return array(
      '#title' => t('Test page'),
      '#markup' => t('Test page text.'),
      '#attached' => $attached,
    );
  }

}
