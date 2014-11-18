<?php

/**
 * @file
 * Contains \Drupal\menu_test\Controller\MenuTestController.
 */

namespace Drupal\menu_test\Controller;

/**
 * Controller routines for menu_test routes.
 */
class MenuTestController {

  /**
   * Page callback: Provides a dummy function which can be used as a placeholder.
   *
   * @return string
   *   A string that can be used for comparison.
   *
   * @see menu_test.routing.yml
   */
  public function menuTestCallback() {
    return ['#markup' => 'This is menu_test_callback().'];
  }

  /**
   * A title callback method for test routes.
   *
   * @param array $_title_arguments
   *   Optional array from the route defaults.
   * @param string $_title
   *   Optional _title string from the route defaults.
   *
   * @return string
   *   The route title.
   */
  public function titleCallback(array $_title_arguments = array(), $_title = '') {
    $_title_arguments += array('case_number' => '2', 'title' => $_title);
    return t($_title_arguments['title']) . ' - Case ' . $_title_arguments['case_number'];
  }

  /**
   * Page callback: Tests the theme negotiation functionality.
   *
   * @param bool $inherited
   *   (optional) TRUE when the requested page is intended to inherit
   *   the theme of its parent.
   *
   * @return string
   *   A string describing the requested custom theme and actual theme being used
   *   for the current page request.
   *
   * @see menu_test.routing.yml
   */
  public function themePage($inherited) {
    $theme_key = \Drupal::theme()->getActiveTheme()->getName();
    // Now we check what the theme negotiator service returns.
    $active_theme = \Drupal::service('theme.negotiator')->determineActiveTheme(\Drupal::routeMatch());
    $output = "Active theme: $active_theme. Actual theme: $theme_key.";
    if ($inherited) {
      $output .= ' Theme negotiation inheritance is being tested.';
    }
    return $output;
  }

  /**
   * A title callback for XSS breadcrumb check.
   *
   * @return string
   */
  public function breadcrumbTitleCallback() {
    return '<script>alert(123);</script>';
  }

}
