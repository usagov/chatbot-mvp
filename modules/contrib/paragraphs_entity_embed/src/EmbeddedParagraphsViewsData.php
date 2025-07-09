<?php

namespace Drupal\paragraphs_entity_embed;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the entity.
 */
class EmbeddedParagraphsViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Creates a relationship to target paragraphs.
    $data['embedded_paragraphs']['paragraph__target_id']['relationship'] = [
      'title' => $this->t('Paragraph Data'),
      'help' => $this->t('Access data from target paragraphs embedded via the Paragraphs entity embed module.'),
      'id' => 'standard',
      'base' => 'paragraphs_item_field_data',
      'base field' => 'id',
      'label' => $this->t('Paragraphs'),
    ];

    return $data;
  }

}
