<?php

declare(strict_types=1);

namespace Drupal\paragraphs_entity_embed\Plugin\CKEditor4To5Upgrade;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor4To5UpgradePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\filter\FilterFormatInterface;

// phpcs:disable Drupal.NamingConventions.ValidFunctionName.ScopeNotCamelCaps

/**
 * Provides the CKEditor 4 to 5 upgrade for Paragraphs Entity Embed.
 *
 * @CKEditor4To5Upgrade(
 *   id = "embedded_paragraphs",
 *   cke4_buttons = {},
 *   cke4_plugin_settings = {},
 *   cke5_plugin_elements_subset_configuration = {}
 * )
 */
class EmbeddedParagraphs extends PluginBase implements CKEditor4To5UpgradePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4ToolbarButtonToCKEditor5ToolbarItem(string $cke4_button, HTMLRestrictions $text_format_html_restrictions): ?array {
    // There should be no change in the machine name when upgrading
    // from ckeditor 4 to ckeditor 5.
    return [$cke4_button];
  }

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4SettingsToCKEditor5Configuration(string $cke4_plugin_id, array $cke4_plugin_settings): ?array {
    throw new \OutOfBoundsException();
  }

  /**
   * {@inheritdoc}
   */
  public function computeCKEditor5PluginSubsetConfiguration(string $cke5_plugin_id, FilterFormatInterface $text_format): ?array {
    throw new \OutOfBoundsException();
  }

}
