<?php

/**
 * @file
 * Contains \Drupal\Core\Language\LanguageManager.
 */

namespace Drupal\Core\Language;

use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Class responsible for initializing each language type.
 */
class LanguageManager {

  /**
   * The language negotiation method id for the language manager.
   */
  const METHOD_ID = 'language-default';

  /**
   * The Key/Value Store to use for state.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $state;

  /**
   * An array of language objects keyed by language type.
   *
   * @var array
   */
  protected $languages;

  /**
   * An array of all the available languages keyed by language code.
   *
   * @var array
   */
  protected $languageList;

  /**
   * An array of configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * The language negotiation method plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $negotiatorManager;

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Whether or not the language manager has been initialized.
   *
   * @var bool
   */
  protected $initialized = FALSE;

  /**
   * Whether already in the process of language initialization.
   *
   * @var bool
   */
  protected $initializing = FALSE;

  /**
   * Constructs a new LanguageManager object.
   *
   * @param array $config
   *   An array of configuration.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $negotiator_manager
   *   The language negotiation methods plugin manager
   * @param \Drupal\Core\KeyValueStoreInterface $state
   *   The state key value store.
   */
  public function __construct(array $config, PluginManagerInterface $negotiator_manager, KeyValueStoreInterface $state) {
    $this->config = $config;
    $this->negotiatorManager = $negotiator_manager;
    $this->state = $state;
  }

  /**
   * Initializes each language type to a language object.
   */
  public function init() {
    if ($this->initialized) {
      return;
    }
    if ($this->isMultilingual()) {
      // This is still assumed by various functions to be loaded.
      include_once DRUPAL_ROOT . '/core/includes/language.inc';
    }
    foreach ($this->getLanguageTypes() as $type) {
      $this->getLanguage($type);
    }
    $this->initialized = TRUE;
  }

  /**
   * Sets the $request property and resets all language types.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the current request.
   */
  public function setRequest(Request $request) {
    $this->request = $request;
    $this->reset();
    $this->init();
  }

  /**
   * Returns a language object for the given type.
   *
   * @param string $type
   *   (optional) The language type, e.g. the interface or the content language.
   *   Defaults to \Drupal\Core\Language\Language::TYPE_INTERFACE.
   *
   * @return \Drupal\Core\Language\Language
   *   A language object for the given type.
   */
  public function getLanguage($type = Language::TYPE_INTERFACE) {
    if (isset($this->languages[$type])) {
      return $this->languages[$type];
    }

    // Ensure we have a valid value for this language type.
    $this->languages[$type] = $this->getLanguageDefault();

    if ($this->isMultilingual() && $this->request) {
      if (!$this->initializing) {
        $this->initializing = TRUE;
        $this->languages[$type] = $this->initializeType($type);
        $this->initializing = FALSE;
      }
      // If the current interface language needs to be retrieved during
      // initialization we return the system language. This way t() calls
      // happening during initialization will return the original strings which
      // can be translated by calling t() again afterwards. This can happen for
      // instance while parsing negotiation method definitions.
      elseif ($type == Language::TYPE_INTERFACE) {
        return new Language(array('id' => Language::LANGCODE_SYSTEM));
      }
    }

    return $this->languages[$type];
  }

  /**
   * Initializes the specified language type.
   *
   * @param string $type
   *   The language type to be initialized.
   */
  public function initializeType($type) {
    // Execute the language negotiation methods in the order they were set up
    // and return the first valid language found.
    foreach ($this->getNegotiationForType($type) as $method_id) {
      if (!isset($this->negotiated[$method_id])) {
        $this->negotiated[$method_id] = $this->negotiateLanguage($type, $method_id);
      }

      // Since objects are references, we need to return a clone to prevent the
      // language negotiation method cache from being unintentionally altered.
      // The same methods might be used with different language types based on
      // configuration.
      $language = !empty($this->negotiated[$method_id]) ? clone($this->negotiated[$method_id]) : FALSE;

      if ($language) {
        // Remember the method ID used to detect the language.
        $language->method_id = $method_id;
        return $language;
      }
    }

    // If no other language was found use the default one.
    $language = $this->getLanguageDefault();
    $language->method_id = LanguageManager::METHOD_ID;
    return $language;
  }

  /**
   * Returns the negotiation settings for the specified language type.
   *
   * @param string $type
   *   The type of the language to retireve the negotiation settings for.
   *
   * @returns array
   *   An array of language negotiation method identifiers ordered by method
   *   weight.
   */
  protected function getNegotiationForType($type) {
    // @todo convert to CMI https://drupal.org/node/1827038
    return array_keys(variable_get("language_negotiation_$type", array()));
  }

  /**
   * Performs language negotiation using the specified negotiation method.
   *
   * @param string $type
   *   The language type to be initialized.
   * @param string $method_id
   *   The string identifier of the language negotiation method to use to detect
   *   language.
   *
   * @return \Drupal\Core\Language\Language|FALSE
   *   Negotiated language object for given type and method, FALSE otherwise.
   */
  protected function negotiateLanguage($type, $method_id) {
    global $user;
    $langcode = FALSE;
    $languages = $this->getLanguageList();
    $method = $this->negotiatorManager->getDefinition($method_id);

    if (!isset($method['types']) || in_array($type, $method['types'])) {

      // Check for a cache mode force from settings.php.
      if (settings()->get('page_cache_without_database')) {
        $cache_enabled = TRUE;
      }
      else {
        drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES, FALSE);
        $config = \Drupal::config('system.performance');
        $cache_enabled = $config->get('cache.page.use_internal');
      }

      // If the language negotiation method has no cache preference or this is
      // satisfied we can execute the callback.
      if ($cache = !isset($method['cache']) || $user->isAuthenticated() || $method['cache'] == $cache_enabled;) {
        $negotiator = $this->negotiatorManager->createInstance($method_id, $this->config);
        $negotiator->setLanguageManager($this);
        $langcode = $negotiator->negotiateLanguage($languages, $this->request);
      }
    }

    return isset($languages[$langcode]) ? $languages[$langcode] : FALSE;
  }

  /**
   * Resets the given language type or all types if none specified.
   *
   * @param string|null $type
   *   (optional) The language type to reset as a string, e.g.,
   *   Language::TYPE_INTERFACE, or NULL to reset all language types. Defaults
   *   to NULL.
   */
  public function reset($type = NULL) {
    if (!isset($type)) {
      $this->languages = array();
      $this->initialized = FALSE;
      $this->languageList = NULL;
    }
    elseif (isset($this->languages[$type])) {
      unset($this->languages[$type]);
    }
  }

  /**
   * Returns whether or not the site has more than one language enabled.
   *
   * @return bool
   *   TRUE if more than one language is enabled, FALSE otherwise.
   */
  public function isMultilingual() {
    return ($this->state->get('language_count') ?: 1) > 1;
  }

  /**
   * Returns an array of the available language types.
   *
   * @return array
   *   An array of all language types where the keys of each are the language type
   *   name and its value is its configurability (TRUE/FALSE).
   */
  public function getLanguageTypes() {
    $types = \Drupal::config('system.language.types')->get('all');
    return $types ? $types : array_keys($this->getTypeDefaults());
  }

  /**
   * Returns a list of the built-in language types.
   *
   * @return array
   *   An array of key-values pairs where the key is the language type name and
   *   the value is its configurability (TRUE/FALSE).
   */
  public function getTypeDefaults() {
    return array(
      Language::TYPE_INTERFACE => TRUE,
      Language::TYPE_CONTENT => FALSE,
      Language::TYPE_URL => FALSE,
    );
  }

  /**
   * Returns the language switch links for the given language type.
   *
   * @param $type
   *   The language type.
   * @param $path
   *   The internal path the switch links will be relative to.
   *
   * @return array
   *   A keyed array of links ready to be themed.
   */
  function getLanguageNegotiationSwitchLinks($type, $path) {
    $links = FALSE;
    $negotiation = variable_get("language_negotiation_$type", array());

    foreach ($negotiation as $method_id => $method) {
      if (isset($method['callbacks']['language_switch'])) {
        if (isset($method['file'])) {
          require_once DRUPAL_ROOT . '/' . $method['file'];
        }

        $callback = $method['callbacks']['language_switch'];
        $result = $callback($type, $path);

        if (!empty($result)) {
          // Allow modules to provide translations for specific links.
          \Drupal::moduleHandler()->alter('language_switch_links', $result, $type, $path);
          $links = (object) array('links' => $result, 'method_id' => $method_id);
          break;
        }
      }
    }

    return $links;
  }

  /**
   * Returns a language object representing the site's default language.
   *
   * @return \Drupal\Core\Language\Language
   *   A language object.
   */
  public function getLanguageDefault() {
    // @todo convert to CMI https://drupal.org/node/1827038
    $default_info = variable_get('language_default', Language::$defaultValues);
    return new Language($default_info + array('default' => TRUE));
  }

  /**
    * Returns a list of languages set up on the site.
    *
    * @param $flags
    *   (optional) Specifies the state of the languages that have to be returned.
    *   It can be: Language::STATE_CONFIGURABLE, Language::STATE_LOCKED, Language::STATE_ALL.
    *
    * @return array
    *   An associative array of languages, keyed by the language code, ordered by
    *   weight ascending and name ascending.
    */
  public function getLanguageList($flags = Language::STATE_CONFIGURABLE) {
    // Initialize master language list.
    if (!isset($this->languageList)) {
      // Initialize local language list cache.
      $this->languageList = array();
      // Fill in master language list based on current configuration.
      $default = $this->getLanguageDefault();
      // No language module, so use the default language only.
      $this->languageList = array($default->id => $default);
      // Add the special languages, they will be filtered later if needed.
      $this->languageList += $this->getDefaultLockedLanguages($default->weight);
    }

    // Filter the full list of languages based on the value of the $all flag. By
    // default we remove the locked languages, but the caller may request for
    // those languages to be added as well.
    $filtered_languages = array();

    // Add the site's default language if flagged as allowed value.
    if ($flags & Language::STATE_SITE_DEFAULT) {
      $default = isset($default) ? $default : $this->getLanguageDefault();
      // Rename the default language.
      $default->name = t("Site's default language (@lang_name)", array('@lang_name' => $default->name));
      $filtered_languages['site_default'] = $default;
    }

    foreach ($this->languageList as $id => $language) {
      if (($language->locked && !($flags & Language::STATE_LOCKED)) || (!$language->locked && !($flags & Language::STATE_CONFIGURABLE))) {
        continue;
       }
      $filtered_languages[$id] = $language;
    }

    return $filtered_languages;
  }

  /**
   * Loads a language object from the database.
   *
   * @param string $langcode
   *   The language code.
   *
   * @return \Drupal\core\Language\Language|null
   *   A fully-populated language object or NULL.
   */
  public function loadLanguage($langcode) {
    $languages = $this->getLanguageList(Language::STATE_ALL);
    return isset($languages[$langcode]) ? $languages[$langcode] : NULL;
  }

  /**
   * Returns a list of the default locked languages.
   *
   * @param int $weight
   *   (optional) An integer value that is used as the start value for the
   *   weights of the locked languages.
   *
   * @return array
   *   An array of language objects.
   */
  public function getDefaultLockedLanguages($weight = 0) {
    $languages = array();

    $locked_language = array(
      'default' => FALSE,
      'locked' => TRUE,
      'enabled' => TRUE,
     );
    $languages[Language::LANGCODE_NOT_SPECIFIED] = new Language(array(
      'id' => Language::LANGCODE_NOT_SPECIFIED,
      'name' => t('Not specified'),
      'weight' => ++$weight,
    ) + $locked_language);

    $languages[Language::LANGCODE_NOT_APPLICABLE] = new Language(array(
      'id' => Language::LANGCODE_NOT_APPLICABLE,
      'name' => t('Not applicable'),
      'weight' => ++$weight,
    ) + $locked_language);

    return $languages;
  }

  /**
   * Some common languages with their English and native names.
   *
   * Language codes are defined by the W3C language tags document for
   * interoperability. Language codes typically have a language and optionally,
   * a script or regional variant name. See
   * http://www.w3.org/International/articles/language-tags/ for more information.
   *
   * This list is based on languages available from localize.drupal.org. See
   * http://localize.drupal.org/issues for information on how to add languages
   * there.
   *
   * The "Left-to-right marker" comments and the enclosed UTF-8 markers are to
   * make otherwise strange looking PHP syntax natural (to not be displayed in
   * right to left). See http://drupal.org/node/128866#comment-528929.
   *
   * @return array
   *   An array of language code to language name information.
   *   Language name information itself is an array of English and native names.
   */
  public static function getStandardLanguageList() {
    return array(
      'af' => array('Afrikaans', 'Afrikaans'),
      'am' => array('Amharic', 'አማርኛ'),
      'ar' => array('Arabic', /* Left-to-right marker "‭" */ 'العربية', Language::DIRECTION_RTL),
      'ast' => array('Asturian', 'Asturianu'),
      'az' => array('Azerbaijani', 'Azərbaycanca'),
      'be' => array('Belarusian', 'Беларуская'),
      'bg' => array('Bulgarian', 'Български'),
      'bn' => array('Bengali', 'বাংলা'),
      'bo' => array('Tibetan', 'བོད་སྐད་'),
      'bs' => array('Bosnian', 'Bosanski'),
      'ca' => array('Catalan', 'Català'),
      'cs' => array('Czech', 'Čeština'),
      'cy' => array('Welsh', 'Cymraeg'),
      'da' => array('Danish', 'Dansk'),
      'de' => array('German', 'Deutsch'),
      'dz' => array('Dzongkha', 'རྫོང་ཁ'),
      'el' => array('Greek', 'Ελληνικά'),
      'en' => array('English', 'English'),
      'eo' => array('Esperanto', 'Esperanto'),
      'es' => array('Spanish', 'Español'),
      'et' => array('Estonian', 'Eesti'),
      'eu' => array('Basque', 'Euskera'),
      'fa' => array('Persian, Farsi', /* Left-to-right marker "‭" */ 'فارسی', Language::DIRECTION_RTL),
      'fi' => array('Finnish', 'Suomi'),
      'fil' => array('Filipino', 'Filipino'),
      'fo' => array('Faeroese', 'Føroyskt'),
      'fr' => array('French', 'Français'),
      'fy' => array('Frisian, Western', 'Frysk'),
      'ga' => array('Irish', 'Gaeilge'),
      'gd' => array('Scots Gaelic', 'Gàidhlig'),
      'gl' => array('Galician', 'Galego'),
      'gsw-berne' => array('Swiss German', 'Schwyzerdütsch'),
      'gu' => array('Gujarati', 'ગુજરાતી'),
      'he' => array('Hebrew', /* Left-to-right marker "‭" */ 'עברית', Language::DIRECTION_RTL),
      'hi' => array('Hindi', 'हिन्दी'),
      'hr' => array('Croatian', 'Hrvatski'),
      'ht' => array('Haitian Creole', 'Kreyòl ayisyen'),
      'hu' => array('Hungarian', 'Magyar'),
      'hy' => array('Armenian', 'Հայերեն'),
      'id' => array('Indonesian', 'Bahasa Indonesia'),
      'is' => array('Icelandic', 'Íslenska'),
      'it' => array('Italian', 'Italiano'),
      'ja' => array('Japanese', '日本語'),
      'jv' => array('Javanese', 'Basa Java'),
      'ka' => array('Georgian', 'ქართული ენა'),
      'kk' => array('Kazakh', 'Қазақ'),
      'km' => array('Khmer', 'ភាសាខ្មែរ'),
      'kn' => array('Kannada', 'ಕನ್ನಡ'),
      'ko' => array('Korean', '한국어'),
      'ku' => array('Kurdish', 'Kurdî'),
      'ky' => array('Kyrgyz', 'Кыргызча'),
      'lo' => array('Lao', 'ພາສາລາວ'),
      'lt' => array('Lithuanian', 'Lietuvių'),
      'lv' => array('Latvian', 'Latviešu'),
      'mg' => array('Malagasy', 'Malagasy'),
      'mk' => array('Macedonian', 'Македонски'),
      'ml' => array('Malayalam', 'മലയാളം'),
      'mn' => array('Mongolian', 'монгол'),
      'mr' => array('Marathi', 'मराठी'),
      'ms' => array('Bahasa Malaysia', 'بهاس ملايو'),
      'my' => array('Burmese', 'ဗမာစကား'),
      'ne' => array('Nepali', 'नेपाली'),
      'nl' => array('Dutch', 'Nederlands'),
      'nb' => array('Norwegian Bokmål', 'Bokmål'),
      'nn' => array('Norwegian Nynorsk', 'Nynorsk'),
      'oc' => array('Occitan', 'Occitan'),
      'pa' => array('Punjabi', 'ਪੰਜਾਬੀ'),
      'pl' => array('Polish', 'Polski'),
      'pt-pt' => array('Portuguese, Portugal', 'Português, Portugal'),
      'pt-br' => array('Portuguese, Brazil', 'Português, Brasil'),
      'ro' => array('Romanian', 'Română'),
      'ru' => array('Russian', 'Русский'),
      'sco' => array('Scots', 'Scots'),
      'se' => array('Northern Sami', 'Sámi'),
      'si' => array('Sinhala', 'සිංහල'),
      'sk' => array('Slovak', 'Slovenčina'),
      'sl' => array('Slovenian', 'Slovenščina'),
      'sq' => array('Albanian', 'Shqip'),
      'sr' => array('Serbian', 'Српски'),
      'sv' => array('Swedish', 'Svenska'),
      'sw' => array('Swahili', 'Kiswahili'),
      'ta' => array('Tamil', 'தமிழ்'),
      'ta-lk' => array('Tamil, Sri Lanka', 'தமிழ், இலங்கை'),
      'te' => array('Telugu', 'తెలుగు'),
      'th' => array('Thai', 'ภาษาไทย'),
      'tr' => array('Turkish', 'Türkçe'),
      'tyv' => array('Tuvan', 'Тыва дыл'),
      'ug' => array('Uyghur', 'Уйғур'),
      'uk' => array('Ukrainian', 'Українська'),
      'ur' => array('Urdu', /* Left-to-right marker "‭" */ 'اردو', Language::DIRECTION_RTL),
      'vi' => array('Vietnamese', 'Tiếng Việt'),
      'xx-lolspeak' => array('Lolspeak', 'Lolspeak'),
      'zh-hans' => array('Chinese, Simplified', '简体中文'),
      'zh-hant' => array('Chinese, Traditional', '繁體中文'),
    );
  }

  /**
   * Returns an array of the available language types.
   *
   * @return array
   *   An array of all language types where the keys of each are the language type
   *   name and its value is its configurability (TRUE/FALSE).
   */
  public function getTypes() {
  }

}
