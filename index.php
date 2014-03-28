<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

$xhprof_path = '/var/www/';
include_once $xhprof_path . '/xhprof_lib/utils/xhprof_lib.php';
include_once $xhprof_path . '/xhprof_lib/utils/xhprof_runs.php';
xhprof_enable(XHPROF_FLAGS_NO_BUILTINS + XHPROF_FLAGS_MEMORY);

require_once __DIR__ . '/core/vendor/autoload.php';
require_once __DIR__ . '/core/includes/bootstrap.inc';

try {
  drupal_handle_request();
}
catch (Exception $e) {
  $message = 'If you have just changed code (for example deployed a new module or moved an existing one) read <a href="http://drupal.org/documentation/rebuild">http://drupal.org/documentation/rebuild</a>';
  if (\Drupal\Component\Utility\Settings::get('rebuild_access', FALSE)) {
    $rebuild_path = $GLOBALS['base_url'] . '/rebuild.php';
    $message .= " or run the <a href=\"$rebuild_path\">rebuild script</a>";
  }
  print $message;
  throw $e;
}

$xhprof_data = xhprof_disable();
$xhprof_runs = new XHProfRuns_Default();
$namespace = 'd8';
$id = $xhprof_runs->save_run($xhprof_data, $namespace);
print "<a href='http://localhost/xhprof_html/?run=$id&sort=excl_wt&source=$namespace'>XHPROF</a>";
