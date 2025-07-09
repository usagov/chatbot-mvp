<?php

namespace Drupal\uswds_ckeditor_integration\Plugin\EmbeddedContent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\embedded_content\EmbeddedContentInterface;
use Drupal\embedded_content\EmbeddedContentPluginBase;

/**
 * Plugin USWDS Accordion for Ckeditor5 Embedded Content.
 *
 * @EmbeddedContent(
 *   id = "uswds_accordion",
 *   label = @Translation("Accordion"),
 *   description = @Translation("Renders a USWDS Accordion."),
 * )
 */
class Accordion extends EmbeddedContentPluginBase implements EmbeddedContentInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'items' => NULL,
      'bordered' => FALSE,
      'multiselect' => FALSE,
      'startcollapsed' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = $this->configuration['items'] ?? NULL;
    if (empty($items)) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No items set.'),
      ];
    }
    foreach ($items as $delta => $item) {
      $items[$delta] = [
        '#heading' => $item['heading'],
        '#body' => [
          '#type' => 'processed_text',
          '#text' => $item['body']['value'],
          '#format' => $item['body']['format'],
        ],
      ];
    }
    return [
      '#theme' => 'uswds_ckeditor_accordion',
      '#items' => $items,
      '#bordered' => $this->configuration['bordered'] ?? NULL,
      '#multiselect' => $this->configuration['multiselect'] ?? NULL,
      '#startcollapsed' => $this->configuration['startcollapsed'] ?? NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $items = $this->configuration['items'] ?? [];

    if (empty($items)) {
      $items[] = [];
    }
    if (!$form_state->get('items')) {
      $form_state->set('items', $items);
    }
    else {
      $items = $form_state->get('items');
    }

    if ($triggeringElement = $form_state->getTriggeringElement()) {
      if (($triggeringElement['#op'] ?? '') == 'remove_item') {
        unset($items[$triggeringElement['#delta']]);
        $form_state->set('items', $items);
      }
      if (($triggeringElement['#op'] ?? '') == 'add_item') {
        $items[] = [];
        $form_state->set('items', $items);
      }
    }

    $form['bordered'] = [
      '#type' => 'checkbox',
      '#title' => 'Bordered',
      '#default_value' => $this->configuration['bordered'] ?? '',
      '#required' => FALSE,
    ];

    $form['multiselect'] = [
      '#type' => 'checkbox',
      '#title' => 'Multiselect',
      '#default_value' => $this->configuration['multiselect'] ?? '',
      '#required' => FALSE,
    ];

    $form['startcollapsed'] = [
      '#type' => 'checkbox',
      '#title' => 'Start collapsed',
      '#default_value' => $this->configuration['startcollapsed'] ?? '',
      '#required' => FALSE,
    ];

    $form['items'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'items-wrapper',
        'style' => 'min-width:800px',
      ],
    ];

    foreach ($items as $delta => $item) {
      $element = [
        '#type' => 'details',
        '#open' => $delta === array_key_last($items),
        '#title' => 'Item',
      ];

      $element['heading'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Heading'),
        '#default_value' => $this->configuration['items'][$delta]['heading'] ?? '',
        '#required' => FALSE,
      ];

      $element['body'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Body'),
        '#format' => $this->configuration['items'][$delta]['body']['format'] ?? 'full_html',
        '#allowed_formats' => ['full_html', 'plain_text'],
        '#default_value' => $this->configuration['items'][$delta]['body']['value'] ?? '',
        '#required' => TRUE,
      ];

      $element['remove_item'] = [
        '#type' => 'button',
        '#limit_validation_errors' => [],
        '#value' => $this->t('Remove item'),
        '#delta' => $delta,
        '#op' => 'remove_item',
        '#name' => 'remove_item_' . $delta,
        '#ajax' => [
          'wrapper' => 'items-wrapper',
          'callback' => [static::class, 'updateItems'],
        ],
      ];
      $form['items'][$delta] = $element;
    }

    $form['add_item'] = [
      '#type' => 'button',
      '#limit_validation_errors' => [],
      '#value' => $this->t('Add item'),
      '#name' => 'add_item',
      '#op' => 'add_item',
      '#ajax' => [
        'wrapper' => 'items-wrapper',
        'callback' => [static::class, 'updateItems'],
      ],
    ];

    return $form;
  }

  /**
   * Update items form element.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form element.
   */
  public static function updateItems(array $form, FormStateInterface $form_state): array {
    return $form['config']['plugin_config']['uswds_accordion']['items'];
  }

  /**
   * {@inheritdoc}
   */
  public function isInline(): bool {
    return FALSE;
  }

}
