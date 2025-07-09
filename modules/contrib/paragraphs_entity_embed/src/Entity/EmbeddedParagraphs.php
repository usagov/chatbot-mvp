<?php

namespace Drupal\paragraphs_entity_embed\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the EmbeddedParagraphs entity.
 *
 * @ingroup EmbeddedParagraphs
 *
 * @ContentEntityType(
 *   id = "embedded_paragraphs",
 *   label = @Translation("EmbeddedParagraphs"),
 *   base_table = "embedded_paragraphs",
 *   revision_table = "embedded_paragraphs_revision",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "revision" = "revision_id",
 *   },
 *   field_ui_base_route = "entity.embedded_paragraphs.edit_form",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\paragraphs_entity_embed\EmbeddedParagraphsViewsData",
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "form" = {
 *       "default" = "Drupal\paragraphs_entity_embed\EmbeddedParagraphsForm",
 *       "add" = "Drupal\paragraphs_entity_embed\EmbeddedParagraphsForm",
 *       "edit" = "Drupal\paragraphs_entity_embed\EmbeddedParagraphsForm",
 *     },
 *     "access" = "Drupal\paragraphs_entity_embed\EmbeddedParagraphsAccessControlHandler",
 *   },
 * )
 */
class EmbeddedParagraphs extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The name of the paragraph entity embed.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ]);

    $fields['paragraph'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Paragraph'))
      ->setSetting('target_type', 'paragraph')
      ->setSetting('handler', 'default:paragraph')
      ->setSetting('handler_settings', ['negate' => 1, 'target_bundles' => []])
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_embed_paragraphs',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_revisions_entity_view',
        'settings' => [
          'view_mode' => 'embed',
        ],
      ]);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->get('id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    return $this->get('uuid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraph() {
    return $this->get('paragraph')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->get('label')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->set('id', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setUuid($uuid) {
    $this->set('uuid', $uuid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setParagraph($paragraph) {
    $this->set('paragraph', $paragraph);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->set('label', $label);
    return $this;
  }

}
