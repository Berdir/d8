<?php

/**
 * @file
 * Contains \Drupal\link\Plugin\Field\FieldWidget\LinkWidget.
 */

namespace Drupal\link\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Url as UrlHelper;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Routing\MatchingRouteNotFoundException;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "link_default",
 *   label = @Translation("Link"),
 *   field_types = {
 *     "link"
 *   },
 *   settings = {
 *     "placeholder_url" = "",
 *     "placeholder_title" = ""
 *   }
 * )
 */
class LinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $url_type = $this->getFieldSetting('url_type');

    $default_url_value = NULL;
    if (isset($items[$delta]->url)) {
      $url = Url::createFromPath($items[$delta]->url);
      $url->setOptions($items[$delta]->options);
      $default_url_value = ltrim($url->toString(), '/');
    }
    $element['url'] = array(
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#placeholder' => $this->getSetting('placeholder_url'),
      '#default_value' => $default_url_value,
      '#maxlength' => 2048,
      '#required' => $element['#required'],
    );

    // If the field is configured to allows internal paths, it cannot use the
    // 'url' form element and we have to do the validation ourselves.
    if ($url_type & LinkItemInterface::LINK_INTERNAL) {
      $element['url']['#type'] = 'textfield';
      $element['#element_validate'][] = array($this, 'validateUrl');
    }

    // If the field is configured to allow only internal paths, add a useful
    // element prefix.
    if ($url_type == LinkItemInterface::LINK_INTERNAL) {
      $element['url']['#field_prefix'] = \Drupal::url('<front>', array(), array('absolute' => TRUE));
    }
    // If the field is configured to allow both internal and external paths,
    // show a useful description.
    elseif ($url_type == LinkItemInterface::LINK_GENERIC) {
      $element['url']['#description'] = $this->t('This can be an internal Drupal path such as %add-node or an external URL such as %drupal. Enter %front to link to the front page.', array('%front' => '<front>', '%add-node' => 'node/add', '%drupal' => 'http://drupal.org'));
    }

    $element['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#placeholder' => $this->getSetting('placeholder_title'),
      '#default_value' => isset($items[$delta]->title) ? $items[$delta]->title : NULL,
      '#maxlength' => 255,
      '#access' => $this->getFieldSetting('title') != DRUPAL_DISABLED,
    );
    // Post-process the title field to make it conditionally required if URL is
    // non-empty. Omit the validation on the field edit form, since the field
    // settings cannot be saved otherwise.
    $is_field_edit_form = ($element['#entity'] === NULL);
    if (!$is_field_edit_form && $this->getFieldSetting('title') == DRUPAL_REQUIRED) {
      $element['#element_validate'][] = array($this, 'validateTitle');
    }

    // Exposing the attributes array in the widget is left for alternate and more
    // advanced field widgets.
    $element['attributes'] = array(
      '#type' => 'value',
      '#tree' => TRUE,
      '#value' => !empty($items[$delta]->options['attributes']) ? $items[$delta]->options['attributes'] : array(),
      '#attributes' => array('class' => array('link-field-widget-attributes')),
    );

    // If cardinality is 1, ensure a label is output for the field by wrapping it
    // in a details element.
    if ($this->fieldDefinition->getCardinality() == 1) {
      $element += array(
        '#type' => 'fieldset',
      );
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['placeholder_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for URL'),
      '#default_value' => $this->getSetting('placeholder_url'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );
    $elements['placeholder_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for link text'),
      '#default_value' => $this->getSetting('placeholder_title'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
      '#states' => array(
        'invisible' => array(
          ':input[name="instance[settings][title]"]' => array('value' => DRUPAL_DISABLED),
        ),
      ),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $placeholder_title = $this->getSetting('placeholder_title');
    $placeholder_url = $this->getSetting('placeholder_url');
    if (empty($placeholder_title) && empty($placeholder_url)) {
      $summary[] = $this->t('No placeholders');
    }
    else {
      if (!empty($placeholder_title)) {
        $summary[] = $this->t('Title placeholder: @placeholder_title', array('@placeholder_title' => $placeholder_title));
      }
      if (!empty($placeholder_url)) {
        $summary[] = $this->t('URL placeholder: @placeholder_url', array('@placeholder_url' => $placeholder_url));
      }
    }

    return $summary;
  }

  /**
   * Form element validation handler; Validates the title property.
   *
   * Conditionally requires the link title if a URL value was filled in.
   */
  public function validateTitle(&$element, &$form_state, $form) {
    if ($element['url']['#value'] !== '' && $element['title']['#value'] === '') {
      $element['title']['#required'] = TRUE;
      \Drupal::formBuilder()->setError($element['title'], $form_state, $this->t('!name field is required.', array('!name' => $element['title']['#title'])));
    }
  }

  /**
   * Form element validation handler; Validates the url property.
   */
  public function validateUrl(&$element, &$form_state, $form) {
    $url_type = $this->getFieldSetting('url_type');
    $url_is_valid = TRUE;

    // Validate only if the field type supports internal URLs.
    if ($element['url']['#value'] !== '' && $url_type & LinkItemInterface::LINK_INTERNAL) {
      try {
        $url = Url::createFromPath($element['url']['#value']);

        if ($url->isExternal() && !UrlHelper::isValid($element['url']['#value'], TRUE)) {
          $url_is_valid = FALSE;
        }
        elseif ($url->isExternal() && $url_type == LinkItemInterface::LINK_INTERNAL) {
          $url_is_valid = FALSE;
        }
      }
      catch (NotFoundHttpException $e) {
        $url_is_valid = FALSE;
      }
      catch (MatchingRouteNotFoundException $e) {
        $url_is_valid = FALSE;
      }
      catch (ParamNotConvertedException $e) {
        $url_is_valid = FALSE;
      }
    }

    if (!$url_is_valid) {
      \Drupal::formBuilder()->setError($element['url'], $form_state, $this->t('The URL %url is not valid.', array('%url' => $element['url']['#value'])));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, array &$form_state) {
    foreach ($values as &$value) {
      if (!empty($value['url'])) {
        try {
          $url = Url::createFromPath($value['url']);
          $url->setOption('attributes', $value['attributes']);

          $value += $url->toArray();
        }
        catch (NotFoundHttpException $e) {
          // Nothing to do here, validateUrl() emits form validation errors.
        }
        catch (MatchingRouteNotFoundException $e) {
          // Nothing to do here, validateUrl() emits form validation errors.
        }
        catch (ParamNotConvertedException $e) {
          // Nothing to do here, validateUrl() emits form validation errors.
        }
      }
    }
    return $values;
  }

}
