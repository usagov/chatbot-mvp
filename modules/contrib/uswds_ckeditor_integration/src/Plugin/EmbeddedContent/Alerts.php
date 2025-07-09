<?php

namespace Drupal\uswds_ckeditor_integration\Plugin\EmbeddedContent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\embedded_content\EmbeddedContentInterface;
use Drupal\embedded_content\EmbeddedContentPluginBase;

/**
 * Plugin USWDS Alerts for Ckeditor5 Embedded Content.
 *
 * @EmbeddedContent(
 *   id = "uswds_alerts",
 *   label = @Translation("Alerts"),
 *   description = @Translation("Renders a USWDS Alert."),
 * )
 */
class Alerts extends EmbeddedContentPluginBase implements EmbeddedContentInterface {

  use StringTranslationTrait;

  const SEVERITY_OPTIONS = [
    'informative' => 'Informative',
    'warning' => 'Warning',
    'error' => 'Error',
    'success' => 'Success',
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'severity' => NULL,
      'slim' => NULL,
      'no_icon' => NULL,
      'heading' => NULL,
      'body' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'uswds_ckeditor_alert',
      '#severity' => $this->configuration['severity'] ?? NULL,
      '#slim' => $this->configuration['slim'] ?? NULL,
      '#no_icon' => $this->configuration['no_icon'] ?? NULL,
      '#heading' => $this->configuration['heading'] ?? NULL,
      '#body' => $this->configuration['body'] ?? NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {

    $form['slim'] = [
      '#type' => 'checkbox',
      '#title' => 'Slim',
      '#default_value' => $this->configuration['slim'],
      '#required' => FALSE,
    ];

    $form['no_icon'] = [
      '#type' => 'checkbox',
      '#title' => 'No Icon',
      '#default_value' => $this->configuration['no_icon'],
      '#required' => FALSE,
    ];

    $form['severity'] = [
      '#type' => 'select',
      '#title' => 'Severity',
      '#default_value' => $this->configuration['severity'],
      '#options' => self::SEVERITY_OPTIONS,
      '#required' => TRUE,
    ];

    $form['heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#default_value' => $this->configuration['heading'],
      '#required' => FALSE,
    ];

    $form['body'] = [
      '#type' => 'textfield',
      '#title' => $this->t('body'),
      '#default_value' => $this->configuration['body'],
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function isInline(): bool {
    return FALSE;
  }

}
