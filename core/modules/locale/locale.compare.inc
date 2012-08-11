<?php

/**
 * @file
 *   The API for comparing project translation status with available translation.
 */

/**
 * Default translation server name.
 *
 * @see locale_translation_default_translation_server().
 */
const LOCALE_TRANSLATION_DEFAULT_SERVER = "localize.drupal.org";

/**
 * Default URL of the xml file at the translation server containing all
 * available languages.
 *
 * @see locale_translation_default_translation_server().
 */
const LOCALE_TRANSLATION_DEFAULT_SERVER_URL = 'http://localize.drupal.org/l10n_server.xml';

/**
 * Default pattern of path and name of the gettext file at the translation
 * server.
 *
 * @see locale_translation_default_translation_server().
 */
const LOCALE_TRANSLATION_DEFAULT_SERVER_PATTERN = 'http://ftp.drupal.org/files/translations/%core/%project/%project-%version.%language.po';

use Drupal\Core\Cache;

/**
 * Get array of projects which are available for interface translation.
 */
function locale_translation_get_projects() {
  $projects = &drupal_static(__FUNCTION__, array());

  if (empty($projects)) {
    // Get project data from the database.
    $projects = array();
    $result = db_query('SELECT * FROM {locale_project}');

    if ($result->rowCount() == 0) {
      // At least the core project should be in the database, so we build the
      // data if none are found.
      locale_translation_build_projects();
      $result = db_query('SELECT * FROM {locale_project}');
    }

    foreach ($result as $project) {
      $projects[$project->name] = $project;
    }
  }
  return $projects;
}

/**
 * Clear the project data table.
 */
function locale_translation_flush_projects() {
  db_truncate('locale_project')->execute();
}

/**
 * Build list of projects and stores the result in the database.
 *
 * Based on l10n_update_build_projects().
 */
function locale_translation_build_projects() {
  // This function depends on Update module. We degrade gracefully.
  if (!module_exists('update')) {
    return array();
  }

  // Get the project list based on .info files.
  $projects = locale_translation_project_list();

  // Mark all previous projects as disabled and store new project data.
  db_update('locale_project')
    ->fields(array(
      'status' => 0,
    ))
    ->execute();

  $default_server = locale_translation_default_translation_server();

  $project_updates = update_get_available(TRUE);
  foreach ($projects as $name => $data) {
    if (isset($project_updates[$name]['releases']) && $project_updates[$name]['project_status'] != 'not-fetched') {
      // Find out if a dev version is installed.
      if (preg_match("/^[0-9]+\.x-([0-9]+)\..*-dev$/", $data['info']['version'], $matches)) {
        // Find a suitable release to use as alternative translation.
        foreach ($project_updates[$name]['releases'] as $project_release) {
          // The first release with the same major release number which is not a
          // dev release is the one. Releases are sorted the most recent first.
          if ($project_release['version_major'] == $matches[1] &&
              (!isset($project_release['version_extra']) || $project_release['version_extra'] != 'dev')) {
            $release = $project_release;
            break;
          }
        }
      }
      elseif ($name == "drupal" || preg_match("/HEAD/", $data['info']['version'], $matches)) {
        // Pick latest available release.
        $release = array_shift($project_updates[$name]['releases']);
      }

      if (!empty($release['version'])) {
        $data['info']['version'] = $release['version'];
      }

      unset($release);
    }

    $data += array(
      'version' => isset($data['info']['version']) ? $data['info']['version'] : '',
      'core' => isset($data['info']['core']) ? $data['info']['core'] : DRUPAL_CORE_COMPATIBILITY,
      // The project can have its own interface translation server, we use
      // default if not.
      'server' => isset($data['info']['interface translation server']) ? $data['info']['interface translation server'] : '',
      // A project can provide the server url to fetch metadata, or the path and
      // filename pattern to download the gettext file.
      'server_url' => isset($data['info']['interface translation server url']) ? $data['info']['interface translation server url'] : '',
      'server_pattern' => isset($data['info']['interface translation server pattern']) ? $data['info']['interface translation server pattern'] : '',
      'status' => $data['project_status'] ? 1 : 0,
    );
    $project = (object) $data;
    // Unless the project provides a full filename pattern, we try to build one.
    if (!isset($project->server_pattern)) {
      $server = NULL;
      if ($project->server || $project->server_url) {
        $server = locale_translation_translation_server($project->server, $project->server_url);
      }
      else {
        // Use the default server.
        $server = locale_translation_translation_server($default_server['server'], $default_server['server_url']);
      }
      if ($server) {
        // Build the update path for this project, with project name and release
        // replaced.
        $project->server_pattern = locale_translation_build_server_pattern($project, $server['update_url']);
      }
    }
    $projects[$name] = $project;

    // Create or update the project record.
    db_merge('locale_project')
      ->key(array('name' => $project->name))
      ->fields(array(
        'name' => $project->name,
        'project_type' => $project->project_type,
        'core' => $project->core,
        'version' => $project->version,
        'server' => $project->server,
        'server_url' => $project->server_url,
        'server_pattern' => $project->server_pattern,
        'status' => $project->status,
      ))
      ->execute();
  }
  return $projects;
}

/**
 * Fetch an array of projects for translation update.
 *
 * @return
 *   Array of project data including .info file data.
 */
function locale_translation_project_list() {
  // This function depends on Update module. We degrade gracefully.
  if (!module_exists('update')) {
    return array();
  }

  $projects = &drupal_static(__FUNCTION__, array());
  if (empty($projects)) {
    module_load_include('compare.inc', 'update');
    $projects = array();

    $additional_whitelist = array(
      'interface translation server',
      'interface translation server url',
      'interface translation server pattern',
      'interface translation project',
    );
    $module_data = _locale_translation_prepare_project_list(system_rebuild_module_data(), 'module');
    $theme_data = _locale_translation_prepare_project_list(system_rebuild_theme_data(), 'theme');
    update_process_info_list($projects, $module_data, 'module', TRUE, $additional_whitelist);
    update_process_info_list($projects, $theme_data, 'theme', TRUE, $additional_whitelist);
    if (variable_get('locale_translation_check_disabled', 0)) {
      update_process_info_list($projects, $module_data, 'module', FALSE, $additional_whitelist);
      update_process_info_list($projects, $theme_data, 'theme', FALSE, $additional_whitelist);
    }

    // Allow other modules to alter projects before fetching and comparing.
    drupal_alter('locale_translation_projects', $projects);
  }
  return $projects;
}

/**
 * Prepare module and theme data.
 *
 * Modify .info file data before it is processed by update_process_info_list().
 * In order for update_process_info_list() to recognize a project, it requires
 * the 'project' parameter in the .info file data.
 * Custom modules or themes can bring their own gettext translation file. To
 * enable import of this file the module or theme defines "interface translation
 * project = myproject" in its .info file. This function will add a project
 * "myproject" to the info data.
 *
 * @param $data
 *   Array of .info file data.
 * @param $type
 *   The project type. i.e. module, theme.
 *
 * @return
 *   Array of .info file data.
 */
function _locale_translation_prepare_project_list($data, $type) {
  $default = array(
    'name' => '',
    'description' => '',
    'type' => '',
    'version' => '',
    'core' => '',
    'status' => '0',
  );

  $projects = locale_translation_get_additional_projects();
  // Merge and add additional project data into the system data.
  foreach ($projects as $name => $project) {
    if ($type == $project['type']) {
      if (isset($data[$name])) {
        $data[$name] = (object)array_merge($project, (array)$data[$name]);
        $data[$name]->info = array_merge($project['info'], $data[$name]->info);
      }
      else {
        $default['status'] = module_exists($name) ? '1' : '0';
        $data[$name] = (object)array_merge($default, $project);
        $data[$name]->info = $project['info'];
      }
    }
  }
  return $data;
}

/**
 * Fetch additional project definitions and definition overrides.
 *
 * @return
 *   Array of custom project.
 *
 * @see hook_locale_translation_additional_project_info().
 */
function locale_translation_get_additional_projects() {
  $projects = &drupal_static(__FUNCTION__, array());

    if (empty($projects)) {
    $hook = 'locale_translation_additional_project_info';

    // Call hook_locale_translation_additional_project_info for modules.
    $projects = module_invoke_all($hook);

    // Call hook_locale_translation_additional_project_info for themes.
    foreach (list_themes() as $theme => $info) {
      $path = drupal_get_path('theme', $theme) . '/template.php';
      if (is_file($path)) {
        include_once $path;
        $function = $theme . '_' . $hook;
        if (function_exists($function)) {
          $projects += $function();
        }
      }
    }
  }

  return $projects;
}

/**
 * Retrieve data for default server.
 *
 * @return
 *   Array of server parameters:
 *   - "server": Localization server name
 *   - "server_url": Localization server URL where language list can be
 *                   retrieved.
 *   - "server_pattern": URL containing po file pattern.
 */
// @todo: Rework the individual vars to a hook_locale_translation_server_info() with sets of server definitions?
function locale_translation_default_translation_server() {
  return array(
    'server' => variable_get('locale_translation_default_server', LOCALE_TRANSLATION_DEFAULT_SERVER),
    'server_url' => variable_get('locale_translation_default_server_url', LOCALE_TRANSLATION_DEFAULT_SERVER_URL),
    'server_pattern' => variable_get('locale_translation_default_server_pattern', LOCALE_TRANSLATION_DEFAULT_SERVER_PATTERN),
  );
}

/**
 * Get server information, that can come from different sources.
 *
 * Possible server sources:
 * - From server list provided by modules. They can provide full server
 *   information or just the URL.
 * - From server_url in a project, we'll fetch the latest data from the server.
 *
 * @param $name
 *   Server name e.g. localize.drupal.org
 * @param $url
 *   Server url
 * @param $refresh
 *   TRUE = refresh the server data.
 *
 * @return
 *   Array of server data.
 */
function locale_translation_translation_servers($name = NULL, $url = NULL, $refresh = FALSE) {
  $info = &drupal_static(__FUNCTION__, array());
  $server_list = &drupal_static(__FUNCTION__ . ':server_list', array());

  // Retrieve server list from modules.
  if (!isset($server_list) || $refresh) {
    $server_list = module_invoke_all('locale_translation_translation_servers');
  }
  // We need at least the server url to fetch all the information.
  if (!$url && $name && isset($server_list[$name])) {
    $url = $server_list[$name]['server_url'];
  }
  // If we still don't have an url, cannot find this server, return false.
  if (!$url) {
    return FALSE;
  }
  // Cache server information based on the url, refresh if asked.
  $cid = 'interface_translation_server:' . $url;
  if ($refresh) {
    unset($info);
    cache('locale')->delete($cid);
  }
  if (!isset($info[$url])) {
    if ($cache = cache('locale')->get($cid)) {
      $info[$url] = $cache->data;
    }
    else {
      module_load_include('fetch.inc', 'locale');
      if ($name && !empty($server_list[$name])) {
        // The name is in our list, it can be full data or just an url.
        $server = $server_list[$name];
      }
      else {
        // This may be a new server provided by a module / package.
        $server = array('name' => $name, 'server_url' => $url);
        // If searching by name, store the name => url mapping.
        if ($name) {
          $server_list[$name] = $server;
        }
      }
      // Now fetch server meta information form the server itself.
      module_load_include('fetch.inc', 'locale');
      if ($server = locale_translation_get_server($server)) {
        cache('locale')->set($cid, $server);
        $info[$url] = $server;
      }
      else {
        // If no server information, this will be FALSE. We won't search a
        // server twice.
        $info[$url] = FALSE;
      }
    }
  }
  return $info[$url];
}

/**
 * Build path to translation source, out of a server path replacement pattern.
 *
 * @param $project
 *   Project object containing data to be inserted in the template.
 * @param $template
 *   String containing place holders. Available place holders:
 *   - "%project": Project name.
 *   - "%version": Poject version.
 *   - "%core": Project core version.
 *   - "%language": Language code.
 *   - "%filename": Project file name.
 *
 * @return string
 *   String with replaced place holders.
 */
function locale_translation_build_server_pattern($project, $template) {
  $variables = array(
    '%project' => $project->name,
    '%version' => $project->version,
    '%core' => $project->core,
    '%language' => isset($project->language) ? $project->language : '%language',
    '%filename' => isset($project->filename) ? $project->filename : '%filename',
  );
  return strtr($template, $variables);
}
