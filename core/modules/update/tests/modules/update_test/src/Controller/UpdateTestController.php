<?php

/**
 * @file
 * Contains \Drupal\update_test\Controller\UpdateTestController.
 */

namespace Drupal\update_test\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides different routes of the update_test module.
 */
class UpdateTestController extends ControllerBase {


  /**
   * Displays an Error 503 (Service unavailable) page.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Returns the response with a special header.
   */
  public function updateError() {
    $response = new Response();
    $response->setStatusCode(503);
    $response->headers->set('Status', '503 Service unavailable');

    return $response;
  }

  /**
   * @todo Remove update_test_mock_page().
   */
  public function updateTest($project_name, $version) {
    $xml_map = $this->config('update_test.settings')->get('xml_map');
    if (isset($xml_map[$project_name])) {
      $availability_scenario = $xml_map[$project_name];
    }
    elseif (isset($xml_map['#all'])) {
      $availability_scenario = $xml_map['#all'];
    }
    else {
      // The test didn't specify (for example, the webroot has other modules and
      // themes installed but they're disabled by the version of the site
      // running the test. So, we default to a file we know won't exist, so at
      // least we'll get an empty xml response instead of a bunch of Drupal page
      // output.
      $availability_scenario = '#broken#';
    }

    $path = drupal_get_path('module', 'update_test');
    $file = "$path/$project_name.$availability_scenario.xml";
    $headers = array('Content-Type' => 'text/xml; charset=utf-8');
    if (!is_file($file)) {
      // Return an empty response.
      return new Response('', 200, $headers);
    }
    return new BinaryFileResponse($file, 200, $headers);
  }

}
