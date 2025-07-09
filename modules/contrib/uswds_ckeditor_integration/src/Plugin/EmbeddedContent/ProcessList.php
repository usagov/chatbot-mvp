<?php

namespace Drupal\uswds_ckeditor_integration\Plugin\EmbeddedContent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\embedded_content\EmbeddedContentInterface;
use Drupal\embedded_content\EmbeddedContentPluginBase;

/**
 * Plugin USWDS Process List for Ckeditor5 Embedded Content.
 *
 * @EmbeddedContent(
 *   id = "uswds_process_list",
 *   label = @Translation("Process List"),
 *   description = @Translation("Renders a USWDS Process List."),
 * )
 */
class ProcessList extends EmbeddedContentPluginBase implements EmbeddedContentInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'process_items' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = $this->configuration['process_items'] ?? NULL;
    if (empty($items)) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No items set.'),
      ];
    }
    foreach ($items as $delta => $item) {
      $items[$delta]['#heading'] = $item['heading'];
      if (!empty($item['body']['value'])) {
        $items[$delta]['#body'] = [
          '#type' => 'processed_text',
          '#text' => $item['body']['value'],
          '#format' => $item['body']['format'],
        ];
      }
    }
    return [
      '#theme' => 'uswds_ckeditor_process_list',
      '#process_items' => $items,
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

    $form['process_items'] = [
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
        '#title' => 'Process Item',
      ];

      $element['heading'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Heading'),
        '#default_value' => $this->configuration['process_items'][$delta]['heading'] ?? '',
        '#required' => TRUE,
      ];

      $element['body'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Body'),
        '#format' => $this->configuration['process_items'][$delta]['body']['format'] ?? 'full_html',
        '#allowed_formats' => ['full_html', 'plain_text'],
        '#default_value' => $this->configuration['process_items'][$delta]['body']['value'] ?? '',
        '#required' => FALSE,
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
      $form['process_items'][$delta] = $element;
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
    return $form['config']['plugin_config']['uswds_process_list']['process_items'];
  }

  /**
   * {@inheritdoc}
   */
  public function isInline(): bool {
    return FALSE;
  }

}
