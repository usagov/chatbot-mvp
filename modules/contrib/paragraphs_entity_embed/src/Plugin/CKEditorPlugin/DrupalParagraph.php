<?php

namespace Drupal\paragraphs_entity_embed\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedCKEditorPluginBase;

/**
 * Defines the "drupalparagraph" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalparagraph",
 *   label = @Translation("Paragraph"),
 *   embed_type_id = "paragraphs_entity_embed"
 * )
 */
class DrupalParagraph extends EmbedCKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return \Drupal::service('extension.list.module')->getPath('paragraphs_entity_embed') . '/js/plugins/drupalparagraph/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'DrupalParagraph_dialogTitleAdd' => $this->t('Insert Paragraph'),
      'DrupalParagraph_dialogTitleEdit' => $this->t('Edit Paragraph'),
      'DrupalParagraph_buttons' => $this->getButtons(),
      'DrupalParagraph_previewCsrfToken' => \Drupal::csrfToken()->get('X-Drupal-EmbedPreview-CSRF-Token'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    $libraries = parent::getLibraries($editor);
    $libraries[] = 'paragraphs_entity_embed/dialog';
    return $libraries;
  }

}
