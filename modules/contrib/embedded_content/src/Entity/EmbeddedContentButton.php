<?php

declare(strict_types=1);

namespace Drupal\embedded_content\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\embedded_content\Form\EmbeddedContentDialogForm;

/**
 * Defines the embedded content entity type.
 *
 * @ConfigEntityType(
 *   id = "embedded_content_button",
 *   label = @Translation("Embedded content button"),
 *   label_collection = @Translation("Embedded content buttons"),
 *   label_singular = @Translation("embedded content button"),
 *   label_plural = @Translation("embedded content buttons"),
 *   label_count = @PluralTranslation(
 *     singular = "@count embedded content button",
 *     plural = "@count embedded content buttons",
 *   ),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\embedded_content\EmbeddedContentButtonListBuilder",
 *     "form" = {
 *       "add" = "Drupal\embedded_content\Form\EmbeddedContentButtonForm",
 *       "edit" = "Drupal\embedded_content\Form\EmbeddedContentButtonForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *   },
 *   config_prefix = "button",
 *   admin_permission = "administer embedded_content",
 *   links = {
 *     "collection" = "/admin/structure/embedded-content",
 *     "add-form" = "/admin/structure/embedded-content/add",
 *     "edit-form" = "/admin/structure/embedded-content/{embedded_content_button}",
 *     "delete-form" = "/admin/structure/embedded-content/{embedded_content_button}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "settings",
 *   },
 * )
 */
final class EmbeddedContentButton extends ConfigEntityBase implements EmbeddedContentButtonInterface {

  use StringTranslationTrait;

  /**
   * The button ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The administrative label.
   *
   * @var string
   */
  protected string $label;

  /**
   * The button settings.
   *
   * @var array
   */
  protected array $settings;

  /**
   * {@inheritdoc}
   */
  public function getIconSvg(): string {
    return $this->getSetting('icon');
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions(): array {
    return array_filter(explode(PHP_EOL, $this->getSetting('conditions') ?? ''));
  }

  /**
   * {@inheritdoc}
   */
  public function meetsCondition(string $plugin_id): bool {
    $patterns = $this->getConditions();
    if (empty($patterns)) {
      return TRUE;
    }
    foreach ($patterns as $pattern) {
      // Allow for regex patterns.
      try {
        if (@preg_match($pattern, $plugin_id)) {
          return TRUE;
        }
      }
      catch (\Throwable $e) {
        // Ignore invalid patterns.
      }
      $pattern = '/^' . str_replace('*', '(.*)', $pattern) . '$/';

      if (@preg_match($pattern, $plugin_id)) {
        return TRUE;
      }

    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconUrl(): string {
    if (!$this->id()) {
      throw new \Exception('Cannot get the icon url on an unsaved button.');
    }
    $url = Url::fromRoute(
          'embedded_content.embedded_content_button.icon', [
            'embedded_content_button' => $this->id(),
          ]
      )->setAbsolute();
    return $url->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return [
      'provider' => 'embedded_content',
      'ckeditor5' => [
        'plugins' => [
          'embeddedContent.EmbeddedContent',
        ],
        'config' => [
          'embeddedContent' => [],
        ],
      ],
      'drupal' => [
        'label' => 'Embedded content',
        'elements' => [
          '<embedded-content>',
          '<embedded-content-inline>',
          '<embedded-content data-plugin-config data-plugin-id data-button-id>',
          '<embedded-content-inline data-plugin-config data-plugin-id data-button-id>',
        ],
        'admin_library' => $this->getAdminLibrary(),
        'class' => 'Drupal\embedded_content\Plugin\CKEditor5Plugin\EmbeddedContent',
        'library' => 'embedded_content/embedded_content',
        'toolbar_items' => [
          'embeddedContent__' . $this->id() => [
            'label' => $this->label(),
          ],
        ],
        'conditions' => [
          'filter' => 'embedded_content',
        ],
      ],
    ];
  }

  /**
   * Get the library for the button.
   *
   * @return string
   *   The library name.
   */
  protected function getAdminLibrary(): string {
    return 'embedded_content/admin';
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting(string|array $value): mixed {
    if (is_string($value)) {
      $value = [$value];
    }
    $settings = $this->getSettings();
    return NestedArray::getValue($settings, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(): array {
    return $this->get('settings') ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDialogSettings(): array {
    $settings = $this->getSettings();
    $dialog_settings = NestedArray::getValue($settings, ['dialog_settings']) ?? [];
    $dialog_settings = NestedArray::filter($dialog_settings);
    return $dialog_settings + [
      'width' => EmbeddedContentDialogForm::DEFAULT_DIALOG_WIDTH,
      'height' => EmbeddedContentDialogForm::DEFAULT_DIALOG_HEIGHT,
      'title' => $this->t(
          'Create @label', [
            '@label' => $this->getSetting('label_singular'),
          ]
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): string {
    return $this->get('label');
  }

  /**
   * {@inheritdoc}
   */
  public function getDialogSetting(string $setting): ?string {
    return $this->getDialogSettings()[$setting] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularLabel(): string {
    return $this->getSetting('label_singular');
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitButtonText():? string {
    return !empty($this->getSetting('submit_button_text')) ? $this->getSetting('submit_button_text') : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getModalTitle():? string {
    return !empty($this->getSetting('modal_title')) ? $this->getSetting('modal_title') : NULL;
  }

}
