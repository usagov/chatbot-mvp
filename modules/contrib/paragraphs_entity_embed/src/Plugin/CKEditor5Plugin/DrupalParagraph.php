<?php

namespace Drupal\paragraphs_entity_embed\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin class to add dialog url for embedded paragraphs.
 */
class DrupalParagraph extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  /**
   * The CSRF Token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfTokenGenerator;

  /**
   * DrupalEntity constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token_generator
   *   The CSRF Token generator service.
   */
  public function __construct(array $configuration,
                              string $plugin_id,
                              CKEditor5PluginDefinition $plugin_definition,
                              CsrfTokenGenerator $csrf_token_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->csrfTokenGenerator = $csrf_token_generator;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('csrf_token'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    // Register embed buttons as individual buttons on admin pages.
    $dynamic_plugin_config = $static_plugin_config;

    // Get the paragraph embed buttons.
    $paragraph_embed_buttons = self::loadParagraphEntityEmbedButtons();

    $buttons = [];
    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    foreach ($paragraph_embed_buttons as $embed_button) {
      $id = $embed_button->id();
      $label = Html::escape($embed_button->label());
      $buttons[$id] = [
        'id' => $id,
        'name' => $label,
        'label' => $label,
        'icon' => $embed_button->getIconUrl(),
      ];
    }

    // Add configured embed buttons and pass it to the UI.
    $dynamic_plugin_config['embeddedParagraph'] = [
      'buttons' => $buttons,
      'format' => $editor->getFilterFormat()->id(),
      'dialogSettings' => [
        'dialogClass' => 'paragraph-select-dialog ckeditor5-paragraph-embed-modal',
        'height' => '75%',
        'width' => '75%',
        'minHeight' => '75%',
        'maxHeight' => 'none',
      ],
      'previewCsrfToken' => $this->csrfTokenGenerator->get('X-Drupal-EmbedPreview-CSRF-Token'),
    ];

    return $dynamic_plugin_config;
  }

  /**
   * Retrieves the paragraph embed buttons.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The paragraph embed buttons.
   */
  public static function loadParagraphEntityEmbedButtons() {
    return \Drupal::entityTypeManager()
      ->getStorage('embed_button')
      ->loadByProperties(['type_id' => 'paragraphs_entity_embed']);
  }

}
