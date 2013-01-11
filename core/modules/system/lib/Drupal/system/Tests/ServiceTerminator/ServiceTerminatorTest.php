<?php

/**
 * @file
 * Contains \Drupal\system\Tests\ServiceTerminator\ServiceTerminatorTest.
 */

namespace Drupal\system\Tests\ServiceTerminator;

use Drupal\simpletest\DrupalUnitTestBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Tests the service terminator service.
 */
class ServiceTerminatorTest extends DrupalUnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Service terminator',
      'description' => 'Tests that services are correctly terminated.',
      'group' => 'DrupalKernel',
    );
  }

  /**
   * Verifies that services are correctly terminated.
   */
  public function testTermination() {
    // Enable the test module to add it to the container.
    $this->enableModules(array('bundle_test'));

    // The service has not been terminated yet.
    $this->assertNull(state()->get('bundle_test.terminated'));

    // Get the service terminator.
    $service_terminator = $this->container->get('kernel_terminate_subscriber');

    // Simulate a shutdown. The test class has not been called, so it should not
    // be terminated.
    $response = new Response();
    $event = new PostResponseEvent($this->container->get('kernel'), $this->container->get('request'), $response);
    $service_terminator->onKernelTerminate($event);
    $this->assertNull(state()->get('bundle_test.terminated'));

    // Now call the class and then terminate again.
    $this->container->get('bundle_test_class');
    $service_terminator->onKernelTerminate($event);
    $this->assertTrue(state()->get('bundle_test.terminated'));
  }

}
