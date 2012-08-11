<?php

/**
 * Alter the list of projects to be updated by locale's interface translation.
 *
 * @todo more description.
 *
 * @param $projects
 *   Array of project data.
 *
 * @see locale_project_list().
 */
function hook_locale_translation_projects_alter(&$projects) {
  // The $projects array contains the project data returned by
  // update_get_projects(). A number of the array elements are described in
  // the documentation of hook_update_projects_alter().

  // In the .info file of a project a localization server can be specified.
  // Using this hook the localization server specification can be altered or
  // added. The 'interface translation server pattern' element is optional but
  // can be specified to override the translation download path specified in the
  // 10n_server.xml file.
  $projects['existing_example_project'] = array(
    'info' => array(
      'interface translation server' => 'example.com',
      'interface translation server url' => 'http://example.com/files/translations/l10n_server.xml',
      'interface translation server pattern' => 'http://example.com/files/translations/%core/%project/%project-%release.%language.po',
    ),
  );
}

/**
 * Allows modules and themes to define or alter project definitions for
 * interface translation.
 *
 * Themes can implement this hook too in their template.php file.
 *
 * @return
 *   Array of project defintions.
 */
function hook_locale_translation_additional_project_info() {
  // If your custom module contains new strings the Locale interface translation
  // can be configured to recognize and import the translations.
  // The tanslations can be located in the local file system or remotely in a
  // translation server (similar to localization.drupal.org).
  // Required: type. "project" is required if the "example_project" is a custom
  // module and not a contributed module.

  // Po file located at a remote translation server.
  $projects['example_module'] = array(
    'type' => 'module',
    'info' => array(
      'project' => 'example_module',
      'interface translation server' => 'example.com',
      'interface translation server url' => 'http://example.com/files/translations/l10n_server.xml',
      'interface translation server pattern' => 'http://example.com/files/translations/%core/%project/%project-%version.%language.po',
    ),
  );

  // Po file located in local file system.
  $projects['example_module'] = array(
    'type' => 'module',
    'info' => array(
      'project' => 'example_module',
      'interface translation server pattern' => 'sites/example.com/modules/custom/example_module/%project-%version.%language.po',
    ),
  );

  // When multiple custom modules share the same po file, their project
  // definitions should match. Both "project", "version" and "interface
  // translation server" definitions are used as part of the po file format and
  // should match.
  // In this example the above example module and the other_example_module share
  // the same po file. "project", "version" and "interface translation server
  // pattern" are overridden to match the above po file name as provided by the
  // example_module.
  $projects['other_example_module'] = array(
    'type' => 'module',
    'info' => array(
      'project' => 'example_module',
      'version' => '1.1',
      'interface translation server pattern' => 'sites/example.com/modules/custom/example_module/%project-%version.%language.po',
    ),
  );

  // Themes can implement this hook too in their template.php file (but only
  // in this file!).
  $projects['zen'] = array(
    'type' => 'theme',
    'info' => array(
      'interface translation server' => 'example.com',
      'interface translation server url' => 'http://example.com/files/translations/l10n_server.xml',
      'interface translation server pattern' => 'http://example.com/files/translations/%core/%project/%project-%version.%language.po',
     ),
  );

  return $projects;
}

// @todo Remove this list.
// Whish list
// ==========
// Let the translation server provide the name and pattern details, instead
//   of defining it in the .info file or in code.
//   Will this work if an installation profile or a custom module provides
//   it's own info hook and needs to download translations from a remote server?
