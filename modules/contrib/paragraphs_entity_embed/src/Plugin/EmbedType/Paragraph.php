<?php

namespace Drupal\paragraphs_entity_embed\Plugin\EmbedType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\embed\EmbedType\EmbedTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Paragraph embed type.
 *
 * @EmbedType(
 *   id = "paragraphs_entity_embed",
 *   label = @Translation("Paragraph")
 * )
 */
class Paragraph extends EmbedTypeBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'enable_paragraph_type_filter' => FALSE,
      'paragraphs_type_filter' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return \Drupal::service('file_url_generator')->generateAbsoluteString(\Drupal::service('extension.list.module')->getPath('paragraphs_entity_embed') . '/icons/paragraph.svg');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['enable_paragraph_type_filter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Filter which Paragraph types to be embed'),
      '#default_value' => $this->getConfigurationValue('enable_paragraph_type_filter'),
    ];
    $form['paragraphs_type_filter'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Paragraph types'),
      '#default_value' => $this->getConfigurationValue('paragraphs_type_filter'),
      '#options' => $this->getAllParagraphTypes(),
      '#states' => [
        'visible' => [':input[name="type_settings[enable_paragraph_type_filter]"]' => ['checked' => TRUE]],
      ],
    ];
    $form['paragraphs_add_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Paragraphs add mode'),
      '#description' => $this->t('Select which add mode to use when selecting a paragraph, the default is a drop down.'),
      '#options' => [
        'dropdown' => $this->t('Dropdown select'),
        'button' => $this->t('Buttons'),
      ],
      '#default_value' => $this->getConfigurationValue('paragraphs_add_mode', 'dropdown'),
    ];

    return $form;
  }

  /**
   * Methods get all paragraph types as options list.
   */
  protected function getAllParagraphTypes() {
    $paragraph_types = [];
    $types = \Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph');
    foreach ($types as $machine_name => $type) {
      $paragraph_types[$machine_name] = $type['label'];
    }
    return $paragraph_types;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->hasAnyErrors()) {
      $this->setConfigurationValue('enable_paragraph_type_filter', $form_state->getValue('enable_paragraph_type_filter'));
      // Set views options.
      $paragraphs_types = $form_state->getValue('enable_paragraph_type_filter') ? array_filter($form_state->getValue('paragraphs_type_filter')) : [];
      $this->setConfigurationValue('paragraphs_type_filter', $paragraphs_types);

    }
  }

}
