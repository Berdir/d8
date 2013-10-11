<?php

/**
 * @file
 * Language Negotiation API.
 *
 * @see http://drupal.org/node/1497272
 */

use Drupal\Core\Language\Language;
use Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSelected;
use Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUI;

/**
 * @defgroup language_negotiation Language Negotiation API functionality
 * @{
 * Functions to customize the language types and the negotiation process.
 *
 * The language negotiation API is based on two major concepts:
 * - Language types: types of translatable data (the types of data that a user
 *   can view or request).
 * - Language negotiation methods: functions for determining which language to
 *   use to present a particular piece of data to the user.
 * Both language types and language negotiation methods are customizable.
 *
 * Drupal defines three built-in language types:
 * - Interface language: The page's main language, used to present translated
 *   user interface elements such as titles, labels, help text, and messages.
 * - Content language: The language used to present content that is available
 *   in more than one language (see
 *   @link field_language Field Language API @endlink for details).
 * - URL language: The language associated with URLs. When generating a URL,
 *   this value will be used by url() as a default if no explicit preference is
 *   provided.
 * Modules can define additional language types through
 * hook_language_types_info(), and alter existing language type definitions
 * through hook_language_types_info_alter().
 *
 * Language types may be configurable or fixed. The language negotiation
 * methods associated with a configurable language type can be explicitly
 * set through the user interface. A fixed language type has predetermined
 * (module-defined) language negotiation settings and, thus, does not appear in
 * the configuration page. Here is a code snippet that makes the content
 * language (which by default inherits the interface language's values)
 * configurable:
 * @code
 * function mymodule_language_types_info_alter(&$language_types) {
 *   unset($language_types[Language::TYPE_CONTENT]['fixed']);
 * }
 * @endcode
 *
 * The locked configuration property prevents one language type from being
 * switched from customized to not customized, and vice versa.
 * @see language_types_set()
 *
 * Every language type can have a different set of language negotiation methods
 * assigned to it. Different language types often share the same language
 * negotiation settings, but they can have independent settings if needed. If
 * two language types are configured the same way, their language switcher
 * configuration will be functionally identical and the same settings will act
 * on both language types.
 *
 * Drupal defines the following built-in language negotiation methods:
 * - URL: Determine the language from the URL (path prefix or domain).
 * - Session: Determine the language from a request/session parameter.
 * - User: Follow the user's language preference.
 * - Browser: Determine the language from the browser's language settings.
 * - Default language: Use the default site language.
 * Language negotiation methods are simple callback functions that implement a
 * particular logic to return a language code. For instance, the URL method
 * searches for a valid path prefix or domain name in the current request URL.
 * If a language negotiation method does not return a valid language code, the
 * next method associated to the language type (based on method weight) is
 * invoked.
 *
 * Modules can define additional language negotiation methods through
 * hook_language_negotiation_info(), and alter existing methods through
 * hook_language_negotiation_info_alter(). Here is an example snippet that lets
 * path prefixes be ignored for administrative paths:
 * @code
 * function mymodule_language_negotiation_info_alter(&$negotiation_info) {
 *   // Replace the core function with our own function.
 *   module_load_include('language', 'inc', 'language.negotiation');
 *   $negotiation_info[\Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl::METHOD_ID]['callbacks']['negotiation'] = 'mymodule_from_url';
 *   $negotiation_info[\Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl::METHOD_ID]['file'] = drupal_get_path('module', 'mymodule') . '/mymodule.module';
 * }
 *
 * function mymodule_from_url($languages) {
 *   // Use the core URL language negotiation method to get a valid language
 *   // code.
 *   module_load_include('language', 'inc', 'language.negotiation');
 *   $langcode = language_from_url($languages);
 *
 *   // If we are on an administrative path, override with the default language.
 *   if (isset($_GET['q']) && strtok($_GET['q'], '/') == 'admin') {
 *     return language_default()->id;
 *   }
 *   return $langcode;
 * }
 * ?>
 * @endcode
 *
 * For more information, see
 * @link http://drupal.org/node/1497272 Language Negotiation API @endlink
 */

/**
 * Returns information about all defined language types.
 *
 * @return
 *   An associative array of language type information arrays keyed by type
 *   names. Based on information from hook_language_types_info().
 *
 * @see hook_language_types_info().
 */
function language_types_info() {
  $language_types = &drupal_static(__FUNCTION__);

  if (!isset($language_types)) {
    $language_types = \Drupal::moduleHandler()->invokeAll('language_types_info');
    // Let other modules alter the list of language types.
    drupal_alter('language_types_info', $language_types);
  }

  return $language_types;
}

/**
 * Returns only the configurable language types.
 *
 * A language type maybe configurable or fixed. A fixed language type is a type
 * whose language negotiation methods are module-defined and not altered through
 * the user interface.
 *
 * @return
 *   An array of language type names.
 */
function language_types_get_configurable() {
  $configurable = \Drupal::config('system.language.types')->get('configurable');
  return $configurable ? $configurable : array();
}

/**
 * Disables the given language types.
 *
 * @param $types
 *   An array of language types.
 */
function language_types_disable($types) {
  $configurable = language_types_get_configurable();
  \Drupal::config('system.language.types')->set('configurable', array_diff($configurable, $types))->save();
}

/**
 * Updates the language type configuration.
 *
 * @param array $configurable_language_types
 *   An array of configurable language types.
 */
function language_types_set(array $configurable_language_types) {
  // Ensure that we are getting the defined language negotiation information. An
  // invocation of \Drupal\Core\Extension\ModuleHandler::install() or
  // \Drupal\Core\Extension\ModuleHandler::uninstall() could invalidate the
  // cached information.
  \Drupal::service('plugin.manager.language_negotiation_method')->clearCachedDefinitions();
  drupal_static_reset('language_types_info');

  $language_types = array();
  $negotiation_info = language_negotiation_info();
  $language_types_info = language_types_info();

  foreach ($language_types_info as $type => $info) {
    $configurable = in_array($type, $configurable_language_types);

    // Check whether the language type is unlocked. Only the status of unlocked
    // language types can be toggled between configurable and non-configurable.
    // The default language negotiation settings, if available, are stored in
    // $info['fixed'].
    if (empty($info['locked'])) {
      // If we have a non-locked non-configurable language type without default
      // language negotiation settings, we use the values negotiated for the
      // interface language which should always be available.
      if (!$configurable && !empty($info['fixed'])) {
        $method_weights = array(LanguageNegotiationUI::METHOD_ID);
        $method_weights = array_flip($method_weights);
        language_negotiation_set($type, $method_weights);
      }
    }
    else {
      // Locked language types with default settings are always considered
      // non-configurable. In turn if default settings are missing, the language
      // type is always considered configurable.
      $configurable = empty($info['fixed']);

      // If the language is non-configurable we need to store its language
      // negotiation settings.
      if (!$configurable) {
        $method_weights = array();
        foreach ($info['fixed'] as $weight => $method_id) {
          if (isset($negotiation_info[$method_id])) {
            $method_weights[$method_id] = $weight;
          }
        }
        language_negotiation_set($type, $method_weights);
      }
    }

    $language_types[$type] = $configurable;
  }

  // Store the language type configuration.
  $config = \Drupal::config('system.language.types');
  $config->set('configurable', array_keys(array_filter($language_types)))->save();
  $config->set('all', array_keys($language_types))->save();
}

/**
 * Returns the ID of the language type's first language negotiation method.
 *
 * @param $type
 *   The language type.
 *
 * @return
 *   The identifier of the first language negotiation method for the given
 *   language type, or the default method if none exists.
 */
function language_negotiation_method_get_first($type) {
  $negotiation = variable_get("language_negotiation_$type", array());
  return empty($negotiation) ? LanguageNegotiationSelected::METHOD_ID : key($negotiation);
}

/**
 * Checks whether a language negotiation method is enabled for a language type.
 *
 * @param $method_id
 *   The language negotiation method ID.
 * @param $type
 *   (optional) The language type. If none is passed, all the configurable
 *   language types will be inspected.
 *
 * @return
 *   TRUE if the method is enabled for at least one of the given language
 *   types, or FALSE otherwise.
 */
function language_negotiation_method_enabled($method_id, $type = NULL) {
  $language_types = !empty($type) ? array($type) : language_types_get_configurable();

  foreach ($language_types as $type) {
    $negotiation = variable_get("language_negotiation_$type", array());
    if (isset($negotiation[$method_id])) {
      return TRUE;
    }
  }

  return FALSE;
}

/**
 * Returns the language switch links for the given language type.
 *
 * @param $type
 *   The language type.
 * @param $path
 *   The internal path the switch links will be relative to.
 *
 * @return
 *   A keyed array of links ready to be themed.
 */
function language_negotiation_get_switch_links($type, $path) {
  return Drupal::languageManager()->getLanguageNegotiationSwitchLinks($type, $path);
}

/**
 * Removes any language negotiation methods that are no longer defined.
 */
function language_negotiation_purge() {
  // Ensure that we are getting the defined language negotiation information. An
  // invocation of \Drupal\Core\Extension\ModuleHandler::install() or
  // \Drupal\Core\Extension\ModuleHandler::uninstall() could invalidate the
  // cached information.
  Drupal::service('plugin.manager.language_negotiation_method')->clearCachedDefinitions();
  drupal_static_reset('language_types_info');

  $negotiation_info = language_negotiation_info();
  foreach (language_types_info() as $type => $type_info) {
    $weight = 0;
    $method_weights = array();
    foreach (variable_get("language_negotiation_$type", array()) as $method_id => $method) {
      if (isset($negotiation_info[$method_id])) {
        $method_weights[$method_id] = $weight++;
      }
    }
    language_negotiation_set($type, $method_weights);
  }
}

/**
 * Saves a list of language negotiation methods for a language type.
 *
 * @param $type
 *   The language type.
 * @param $method_weights
 *   An array of language negotiation method weights keyed by method ID.
 */
function language_negotiation_set($type, $method_weights) {
  // Save only the necessary fields.
  $method_fields = array('callbacks', 'file', 'cache');

  $negotiation = array();
  $negotiation_info = language_negotiation_info();
  $default_types = language_types_get_configurable();

  // Order the language negotiation method list by weight.
  asort($method_weights);

  foreach ($method_weights as $method_id => $weight) {
    if (isset($negotiation_info[$method_id])) {
      $method = $negotiation_info[$method_id];
      // If the language negotiation method does not express any preference
      // about types, make it available for any configurable type.
      $types = array_flip(!empty($method['types']) ? $method['types'] : $default_types);
      // Check whether the method is defined and has the right type.
      if (isset($types[$type])) {
        $method_data = array();
        foreach ($method_fields as $field) {
          if (isset($method[$field])) {
            $method_data[$field] = $method[$field];
          }
        }
        $negotiation[$method_id] = $method_data;
      }
    }
  }

  variable_set("language_negotiation_$type", $negotiation);
}

/**
 * Returns all defined language negotiation methods.
 *
 * @return
 *   An array of language negotiation methods.
 */
function language_negotiation_info() {
  return Drupal::service('plugin.manager.language_negotiation_method')->getDefinitions();
}

/**
 * Returns the possible fallback languages ordered by language weight.
 *
 * @param
 *   (optional) The language type. Defaults to Language::TYPE_CONTENT.
 *
 * @return
 *   An array of language codes.
 */
function language_fallback_get_candidates($type = Language::TYPE_CONTENT) {
  $fallback_candidates = &drupal_static(__FUNCTION__);

  if (!isset($fallback_candidates)) {
    // Get languages ordered by weight, add Language::LANGCODE_NOT_SPECIFIED at the end.
    $fallback_candidates = array_keys(language_list());
    $fallback_candidates[] = Language::LANGCODE_NOT_SPECIFIED;

    // Let other modules hook in and add/change candidates.
    drupal_alter('language_fallback_candidates', $fallback_candidates);
  }

  return $fallback_candidates;
}

/**
 * @} End of "language_negotiation"
 */
