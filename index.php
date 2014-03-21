<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

$xhprof_path = '/var/www/html';
include_once $xhprof_path . '/xhprof_lib/utils/xhprof_lib.php';
include_once $xhprof_path . '/xhprof_lib/utils/xhprof_runs.php';
xhprof_enable(XHPROF_FLAGS_MEMORY);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);

$xhprof_data = xhprof_disable();
$xhprof_runs = new XHProfRuns_Default();
$namespace = 'd8';
$id = $xhprof_runs->save_run($xhprof_data, $namespace);
print "<a href='http://localhost/xhprof_html/?run=$id&sort=excl_wt&source=$namespace'>XHPROF</a>";
