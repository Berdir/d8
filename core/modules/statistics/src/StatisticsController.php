<?php

/**
 * @file
 * Contains \Drupal\statistics\StatisticsController.
 */

namespace Drupal\statistics;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles responses for Statistics module routes.
 */
class StatisticsController extends ControllerBase {

  /**
   * Handles counts of node views via AJAX.
   */
  public function updateNodeViewCounter() {
    $views = \Drupal::config('statistics.settings')->get('count_content_views');
    if ($views) {
      $nid = filter_input(INPUT_POST, 'nid', FILTER_VALIDATE_INT);
      if ($nid) {
        \Drupal::database()->merge('node_counter')
          ->key('nid', $nid)
          ->fields(array(
            'daycount' => 1,
            'totalcount' => 1,
            'timestamp' => REQUEST_TIME,
          ))
          ->expression('daycount', 'daycount + 1')
          ->expression('totalcount', 'totalcount + 1')
          ->execute();
      }
    }
    return new Response();
  }

}

