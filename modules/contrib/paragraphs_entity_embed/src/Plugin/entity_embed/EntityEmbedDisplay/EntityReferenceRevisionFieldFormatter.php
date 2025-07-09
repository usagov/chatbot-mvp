<?php

namespace Drupal\paragraphs_entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\EntityReferenceFieldFormatter;

/**
 * Entity Embed Display reusing entity reference revisions field formatters.
 *
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference_revisions",
 *   label = @Translation("Entity Reference Revision"),
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "entity_reference_revisions",
 *   supports_image_alt_and_title = TRUE
 * )
 */
class EntityReferenceRevisionFieldFormatter extends EntityReferenceFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldValue() {
    return [
      'entity' => $this->getContextValue('entity'),
    ];
  }

}
