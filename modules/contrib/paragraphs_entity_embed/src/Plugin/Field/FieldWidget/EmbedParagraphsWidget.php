<?php

namespace Drupal\paragraphs_entity_embed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\EntityReferenceSelection\ParagraphSelection;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Plugin implementation of the 'entity_reference embed paragraphs' widget.
 *
 * We hide add / remove buttons when translating to avoid accidental loss of
 * data because these actions effect all languages.
 *
 * @FieldWidget(
 *   id = "entity_reference_embed_paragraphs",
 *   label = @Translation("Embed Paragraphs"),
 *   description = @Translation("A embed paragraphs inline form widget."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class EmbedParagraphsWidget extends ParagraphsWidget {

  /**
   * Embed button that use this widget.
   *
   * @var string
   */
  protected $embedButton;

  /**
   * Returns the sorted allowed types for a entity reference field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   (optional) The field definition for which the allowed types should be
   *   returned, defaults to the current field.
   *
   * @return array
   *   A list of arrays keyed by the paragraph type machine name with the
   *   following properties.
   *     - label: The label of the paragraph type.
   *     - weight: The weight of the paragraph type.
   */
  public function getAllowedTypes(FieldDefinitionInterface $field_definition = NULL) {
    $return_bundles = [];
    /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager */
    $selection_manager = \Drupal::service('plugin.manager.entity_reference_selection');
    $handler = $selection_manager->getSelectionHandler($this->fieldDefinition);
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($this->getFieldSetting('target_type'));

    if (!empty($this->embedButton->type_settings['enable_paragraph_type_filter'])) {
      $weight = 0;
      foreach ($this->embedButton->type_settings['paragraphs_type_filter'] as $bundle) {
        $return_bundles[$bundle] = [
          'label' => !empty($bundles[$bundle]) ? $bundles[$bundle]['label'] : $bundle,
          'weight' => $weight,
        ];
        $weight++;
      }
    }
    elseif ($handler instanceof ParagraphSelection) {
      $return_bundles = $handler->getSortedAllowedTypes();
    }
    // Support for other reference types.
    else {
      $weight = 0;
      foreach ($bundles as $machine_name => $bundle) {
        if (!count($this->getSelectionHandlerSetting('target_bundles')) || in_array($machine_name, $this->getSelectionHandlerSetting('target_bundles'))) {

          $return_bundles[$machine_name] = [
            'label' => $bundle['label'],
            'weight' => $weight,
          ];

          $weight++;
        }
      }
    }

    return $return_bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    // Set Embed button.
    $storage = $form_state->getStorage();
    $this->embedButton = $storage['editorParams']['embed_button'] ?? [];

    if (!empty($this->embedButton->type_settings['paragraphs_add_mode'])) {
      // Set the add mode specified.
      $this->settings['add_mode'] = $this->embedButton->type_settings['paragraphs_add_mode'];
    }

    return parent::formMultipleElements($items, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeaderActions(array $field_state, FormStateInterface $form_state) {
    // Build header actions are not needed on embedded paragraphs.
    return [];
  }

}
