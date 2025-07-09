<?php

namespace Drupal\uswds_ckeditor_integration\Plugin\CKEditor5Plugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Uswds Grid Config for CKE5.
 */
class UswdsGrid extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface, CKEditor5PluginConfigurableInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'));
  }

  /**
   * Grid Config constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(array $configuration, string $plugin_id, CKEditor5PluginDefinition $plugin_definition, protected ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration(): array {
    $available_columns = range(1, 12);
    return [
      'available_columns' => $available_columns,
      'available_breakpoints' => [
        'card' => 'card',
        'card_lg' => 'card_lg',
        'mobile' => 'mobile',
        'mobile_lg' => 'mobile_lg',
        'tablet' => 'tablet',
        'tablet_lg' => 'tablet_lg',
        'desktop' => 'desktop',
        'desktop_lg' => 'desktop_lg',
        'widescreen' => 'widescreen',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $dynamic_plugin_config = $static_plugin_config;

    $dynamic_plugin_config['uswdsGrid']['dialogURL'] = Url::fromRoute('uswds_ckeditor_integration.dialog')
      ->setRouteParameter('editor', $editor->getFilterFormat()->id())
      ->toString(TRUE)
      ->getGeneratedUrl();

    $dynamic_plugin_config['uswdsGrid'] = array_merge(
      $dynamic_plugin_config['uswdsGrid'],
      $this->getConfiguration()
    );

    return $dynamic_plugin_config;
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $available_columns = array_combine($r = range(1, 12), $r);
    $form['available_columns'] = [
      '#title' => $this->t('Allowed Columns'),
      '#type' => 'checkboxes',
      '#options' => $available_columns,
      '#default_value' => $this->configuration['available_columns'],
      '#required' => TRUE,
    ];

    $bs_breakpoints = $this->configFactory->get('uswds_ckeditor_integration.settings')->get('breakpoints');
    $breakpoint_options = [];
    foreach ($bs_breakpoints as $class => $breakpoint) {
      $breakpoint_options[$class] = $breakpoint['label'];
    }
    $form['available_breakpoints'] = [
      '#title' => $this->t('Allowed Breakpoints'),
      '#type' => 'checkboxes',
      '#options' => $breakpoint_options,
      '#default_value' => $this->configuration['available_breakpoints'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setValue('available_columns', array_values(array_filter($form_state->getValue('available_columns'))));
    $form_state->setValue('available_breakpoints', array_values(array_filter($form_state->getValue('available_breakpoints'))));
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['available_columns'] = $form_state->getValue('available_columns');
    $this->configuration['available_breakpoints'] = $form_state->getValue('available_breakpoints');
  }

}
