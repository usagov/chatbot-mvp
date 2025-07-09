<?php

namespace Drupal\uswds_paragraph_components\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Plugin implementation of the 'entity_reference_revisions paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "uswds_paragraph_components_paragraphs",
 *   label = @Translation("Extended Paragraphs (stable) - USWDS Breakpoints"),
 *   description = @Translation("Extended paragraphs inline form widget."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class UswdsParagraphsBreakpoints extends ParagraphsWidget {

  /**
   * Shows minimum widgets on the content add form.
   *
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state): array {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];
    $field_state = parent::getWidgetState($parents, $field_name, $form_state);

    $vid = 'uswds_breakpoints';
    // @phpstan-ignore-next-line
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $count = 0;
    $form['#custom_tids'] = [];
    foreach ($terms as $term) {
      if ($term->status) {
        $form['#custom_tids'][] = $term->tid;
        $count++;
      }
    }

    if ($field_state['items_count'] < $count - 1) {
      $field_state['items_count'] = $count - 1;
      parent::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    return parent::formMultipleElements($items, $form, $form_state);
  }

  /**
   * Sets default value for minimum fields.
   *
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return ['disable_breakpoints' => FALSE] + parent::defaultSettings();
  }

  /**
   * Populate settings form to input minimum fields value.
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element['disable_breakpoints'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Breakpoints field'),
      '#default_value' => $this->getSetting('disable_breakpoints'),
      '#description' => $this->t('Check to disable breakpoints field.'),
    ];

    return $element + parent::settingsForm($form, $form_state);
  }

  /**
   * Shows settings summary for fields at form Manage Form Display.
   *
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    $disable_breakpoints = $this->getSetting('disable_breakpoints');
    if (!empty($disable_breakpoints)) {
      $summary[] = $this->t('Disable Breakpoints Field: @disable_breakpoints', ['@disable_breakpoints' => $disable_breakpoints]);
    }

    return array_merge($summary, parent::settingsSummary());
  }

}
