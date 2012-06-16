<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

// Bootstrap all of Drupal's subsystems, but do not initialize anything that
// depends on the fully resolved Drupal path, because path resolution happens
// during the REQUEST event of the kernel.
// @see Drupal\Core\EventSubscriber\PathSubscriber;
// @see Drupal\Core\EventSubscriber\LegacyRequestSubscriber;
require_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_CODE);

// @todo Figure out how best to handle the Kernel constructor parameters.
$kernel = new DrupalKernel('prod', FALSE);

$start = microtime(TRUE);
for ($i = 0; $i < 1000; $i++) {
  $entity = entity_create('entity_test', array('name' => 'a'));
  $entity->save();
  var_dump($entity->id());
}
for ($i = 1; $i <= 1; $i++) {
  $entity = entity_load('entity_test', $i);
  var_dump($i);
}
$end = microtime(TRUE);
var_dump($end - $start);

