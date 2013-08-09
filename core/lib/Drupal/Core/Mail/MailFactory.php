<?php

/**
 * @file
 * Contains Drupal\Core\Mail\MailFactory.
 */

namespace Drupal\Core\Mail;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Component\Utility\String;

/**
 * Factory for creating mail system objects.
 */
class MailFactory {

  /**
   * Holds mail interface configurations
   *
   * @var array
   */
  protected $configuration;

  /**
   * List of already instantiated mail system objects.
   *
   * @var array
   */
  protected $instances = array();

  public function __construct(ConfigFactory $configFactory) {
    $this->configuration = $configFactory->get('system.mail')->get('interface');
  }


  /**
   * Returns an object that implements \Drupal\Core\Mail\MailInterface.
   *
   * Allows for one or more custom mail backends to format and send mail messages
   * composed using drupal_mail().
   *
   * An implementation needs to implement the following methods:
   * - format: Allows to preprocess, format, and postprocess a mail
   *   message before it is passed to the sending system. By default, all messages
   *   may contain HTML and are converted to plain-text by the
   *   Drupal\Core\Mail\PhpMail implementation. For example, an alternative
   *   implementation could override the default implementation and additionally
   *   sanitize the HTML for usage in a MIME-encoded e-mail, but still invoking
   *   the Drupal\Core\Mail\PhpMail implementation to generate an alternate
   *   plain-text version for sending.
   * - mail: Sends a message through a custom mail sending engine.
   *   By default, all messages are sent via PHP's mail() function by the
   *   Drupal\Core\Mail\PhpMail implementation.
   *
   * The selection of a particular implementation is controlled via the config
   * 'system.mail.interface', which is a keyed array.  The default implementation
   * is the class whose name is the value of 'default' key. A more specific match
   * first to key and then to module will be used in preference to the default. To
   * specify a different class for all mail sent by one module, set the class
   * name as the value for the key corresponding to the module name. To specify
   * a class for a particular message sent by one module, set the class name as
   * the value for the array key that is the message id, which is
   * "${module}_${key}".
   *
   * For example to debug all mail sent by the user module by logging it to a
   * file, you might set the variable as something like:
   *
   * @code
   * array(
   *   'default' => 'Drupal\Core\Mail\PhpMail',
   *   'user' => 'DevelMailLog',
   * );
   * @endcode
   *
   * Finally, a different system can be specified for a specific e-mail ID (see
   * the $key param), such as one of the keys used by the contact module:
   *
   * @code
   * array(
   *   'default' => 'Drupal\Core\Mail\PhpMail',
   *   'user' => 'DevelMailLog',
   *   'contact_page_autoreply' => 'DrupalDevNullMailSend',
   * );
   * @endcode
   *
   * Other possible uses for system include a mail-sending class that actually
   * sends (or duplicates) each message to SMS, Twitter, instant message, etc, or
   * a class that queues up a large number of messages for more efficient bulk
   * sending or for sending via a remote gateway so as to reduce the load
   * on the local server.
   *
   * @param string $module
   *   The module name which was used by drupal_mail() to invoke hook_mail().
   * @param string $key
   *   A key to identify the e-mail sent. The final e-mail ID for the e-mail
   *   alter hook in drupal_mail() would have been {$module}_{$key}.
   *
   * @return \Drupal\Core\Mail\MailInterface
   *   An object that implements Drupal\Core\Mail\MailInterface.
   *
   * @throws \Exception
   */
  public function get($module, $key) {
    $this->instances = &drupal_static(__FUNCTION__, array());

    $id = $module . '_' . $key;

    // Look for overrides for the default class, starting from the most specific
    // id, and falling back to the module name.
    if (isset($this->configuration[$id])) {
      $class = $this->configuration[$id];
    }
    elseif (isset($this->configuration[$module])) {
      $class = $this->configuration[$module];
    }
    else {
      $class = $this->configuration['default'];
    }

    if (empty($this->instances[$class])) {
      $interfaces = class_implements($class);
      if (isset($interfaces['Drupal\Core\Mail\MailInterface'])) {
        $this->instances[$class] = new $class();
      }
      else {
        throw new \Exception(String::format('Class %class does not implement interface %interface', array('%class' => $class, '%interface' => 'Drupal\Core\Mail\MailInterface')));
      }
    }
    return $this->instances[$class];
  }


} 
