<?php

declare(strict_types=1);

namespace Drupal\uswds_ckeditor_integration\Plugin\CKEditor5Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 USWDS class overrides.
 *
 * @internal
 *   Plugin classes are internal.
 */
class UswdsOverrideDefaults extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'override_links' => FALSE,
      'override_tables' => FALSE,
      'override_lists' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {

    $form['override_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use USWDS classes for links.'),
      '#default_value' => $this->configuration['override_links'],
    ];

    $form['override_tables'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use USWDS classes for tables.'),
      '#default_value' => $this->configuration['override_tables'],
    ];

    $form['override_lists'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use USWDS classes for lists.'),
      '#default_value' => $this->configuration['override_lists'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $form_value = $form_state->getValue('override_links');
    $form_state->setValue('override_links', (bool) $form_value);

    $form_value = $form_state->getValue('override_tables');
    $form_state->setValue('override_tables', (bool) $form_value);

    $form_value = $form_state->getValue('override_lists');
    $form_state->setValue('override_lists', (bool) $form_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['override_links'] = $form_state->getValue('override_links');
    $this->configuration['override_tables'] = $form_state->getValue('override_tables');
    $this->configuration['override_lists'] = $form_state->getValue('override_lists');
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config[] = array_merge(
      $static_plugin_config,
      $this->getConfiguration()
    );

    return [
      'uswds' => [
        'options' => $static_plugin_config,
      ],
    ];
  }

}
