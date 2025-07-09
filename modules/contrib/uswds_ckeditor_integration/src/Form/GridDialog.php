<?php

namespace Drupal\uswds_ckeditor_integration\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\SetDialogTitleCommand;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\editor\EditorInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a grid dialog form for use in CKEditor.
 *
 * @package Drupal\uswds_ckeditor_integration\Form
 */
class GridDialog extends FormBase {

  /**
   * GridDialog class initialize.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   */
  public function __construct(protected FormBuilderInterface $formBuilder, protected ModuleExtensionList $moduleExtensionList) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('form_builder'),
      $container->get('extension.list.module')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'uswds_ckeditor_integration_dialog';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?Editor $editor = NULL): array {
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#attached']['library'][] = 'uswds_ckeditor_integration/dialog';

    // Opening the dialog passes the value as editor object, but is only once.
    $values = $form_state->getValues();
    $input = $form_state->getUserInput();

    // Initialize entity element with form attributes, if present.
    $settings = empty($values['uswds_grid_settings']) ? [] : Json::decode($values['uswds_grid_settings']);
    $settings += empty($input['uswds_grid_settings']) ? [] : Json::decode($input['uswds_grid_settings']);
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    if (!$form_state->get('uswds_grid_settings')) {
      $form_state->set('uswds_grid_settings', $input['editor_object'] ?? []);
    }
    $settings += $form_state->get('uswds_grid_settings');

    // Save the editor settings.
    if ($editor instanceof EditorInterface) {
      $editor_settings = $editor->getSettings();
      // CKE5 support.
      if (isset($editor_settings['plugins']['uswds_ckeditor_integration_grid'])) {
        $settings['editor_settings'] = $editor_settings['plugins']['uswds_ckeditor_integration_grid'];
      }
    }
    $form_state->set('uswds_grid_settings', $settings);

    if (!$form_state->get('step')) {
      $form_state->set('step', 'select');
    }

    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div id="uswds_grid-dialog-form">';
    $form['#suffix'] = '</div>';
    $form['#attributes']['class'][] = 'uswds_grid-dialog-step--' . $form_state->get('step');

    if ($form_state->get('step') == 'select') {
      $form = $this->buildSelectStep($form, $form_state);
    }
    elseif ($form_state->get('step') == 'layout') {
      $form = $this->buildLayoutStep($form, $form_state);
    }
    elseif ($form_state->get('step') == 'advanced') {
      $form = $this->buildAdvancedStep($form, $form_state);
    }

    return $form;
  }

  /**
   * Builds the column selection step.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form part.
   */
  public function buildSelectStep(array $form, FormStateInterface $form_state): array {
    $settings = $form_state->get('uswds_grid_settings');
    $form['#title'] = $this->t("Select columns");

    $columns = [];
    if (isset($settings['editor_settings']['available_columns'])) {
      $available_cols = array_filter($settings['editor_settings']['available_columns']);
      foreach ($available_cols as $column) {
        $title = $this->t('Column @num', ['@num' => $column]);
        $img = $this->moduleExtensionList->getPath('uswds_ckeditor_integration') . '/images/ui/col_' . $column . '.png';
        $img_src = '<img src="/' . $img . '" title="' . $title . '" />';
        $columns[$column] = $img_src . '<p>' . $this->t('Column @num', ['@num' => $column]) . '</p>';
      }
    }

    if (!empty($settings['saved'])) {
      $form['uswds_grid_settings'] = [
        '#type' => 'hidden',
        '#value' => Json::encode($settings),
      ];
    }

    // @todo Make this configurable from text format.
    $form['num_columns'] = [
      '#title' => $this->t('Select Number of Columns'),
      '#type' => 'radios',
      '#options' => $columns,
      '#default_value' => $settings['num_columns'] ?? 1,
      '#attributes' => [
        'disabled' => $settings['saved'] ?? FALSE,
      ],
      '#prefix' => $this->t('Read-only on existing elements.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitStep',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Builds the layout selection step.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form part.
   */
  public function buildLayoutStep(array $form, FormStateInterface $form_state): array {
    $config = $this->config('uswds_ckeditor_integration.settings');
    $settings = $form_state->get('uswds_grid_settings');
    $form['#title'] = $this->t("Choose a layout");

    $form['add_container'] = [
      '#title' => $this->t('Add Container'),
      '#description' => $this->t('Add grid-container class to wrapper.'),
      '#type' => 'checkbox',
      '#default_value' => $settings['add_container'] ?? FALSE,
      '#attributes' => [
        'class' => ['uswds_grid-add-container'],
      ],
    ];

    // @todo detect row class override.
    $form['no_gutter'] = [
      '#title' => $this->t('No Gutters'),
      '#type' => 'checkbox',
      '#default_value' => $settings['no_gutter'] ?? FALSE,
    ];

    // Default.
    $num_cols = (int) $settings['num_columns'];

    $available_breakpoints = array_filter($settings['editor_settings']['available_breakpoints']);
    $available_breakpoints = array_combine($available_breakpoints, $available_breakpoints);
    foreach ($config->get('breakpoints') as $class => $breakpoint) {
      if (!isset($available_breakpoints[$class])) {
        continue;
      }

      $options = [
        'none' => $this->t('None (advanced)'),
      ];
      $prefix = $breakpoint['prefix'];

      $form['breakpoints'][$prefix] = [
        '#title' => $breakpoint['label'],
        '#type' => 'details',
        '#open' => FALSE,
      ];

      foreach ($breakpoint['columns'][$num_cols]['layouts'] as $layout) {
        $options[implode('_', $layout['settings'])] = $layout['label'];
      }

      $form['breakpoints'][$prefix]['layout'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#default_value' => $settings['breakpoints'][$prefix]['layout'] ?? 'none',
        '#attributes' => [
          'data-uswds-grid-option' => TRUE,
        ],
      ];
    }

    // @todo Refactor for repetition here.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitBackStep',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-back',
        ],
      ],
    ];

    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitDialog',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    $form['actions']['advanced'] = [
      '#type' => 'submit',
      '#value' => $this->t('Advanced Settings'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitStep',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Builds the advanced settings step.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form part.
   */
  public function buildAdvancedStep(array $form, FormStateInterface $form_state): array {
    $settings = $form_state->get('uswds_grid_settings');
    $form['#title'] = $this->t("Advanced Settings");

    $form['container_wrapper_class'] = [
      '#title' => $this->t('Container Wrapper Classes'),
      '#type' => 'textfield',
      '#description' => $this->t('Add classes separated by space. Ex: bg-warning py-5'),
      '#default_value' => $settings['container_wrapper_class'] ?? '',
    ];

    $form['row_class'] = [
      '#title' => $this->t('Row Classes'),
      '#type' => 'textfield',
      '#description' => $this->t('Add classes separated by space. Ex: bg-warning py-5'),
      '#default_value' => $settings['row_class'] ?? '',
    ];

    for ($i = 1; $i <= $settings['num_columns']; $i++) {
      $form['col_' . $i . '_classes'] = [
        '#title' => $this->t('Col @num classes', ['@num' => $i]),
        '#type' => 'textfield',
        '#default_value' => $settings['col_' . $i . '_classes'] ?? '',
        '#description' => $this->t('Add classes separated by space. Ex: bg-warning py-5'),
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitBackStep',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-back',
        ],
      ],
    ];

    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitDialog',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Load current settings.
    $settings = $form_state->get('uswds_grid_settings');
    $values_to_save = [];

    if ($form_state->get('step') == 'select') {
      $values_to_save = ['num_columns'];
    }
    elseif ($form_state->get('step') == 'layout') {
      $values_to_save = [
        'add_container',
        'no_gutter',
        'breakpoints',
      ];
    }
    elseif ($form_state->get('step') == 'advanced') {
      $values_to_save = [
        'container_wrapper_class',
        'container_class',
        'row_class',
      ];

      // Detect changes and set parent to "none".
      for ($i = 1; $i <= $settings['num_columns']; $i++) {
        $key = 'col_' . $i . '_classes';
        $settings[$key] = $form_state->getValue($key, '');
      }
    }

    foreach ($values_to_save as $save) {
      $settings[$save] = $form_state->getValue($save) ?? '';
    }
    $form_state->set('uswds_grid_settings', $settings);
  }

  /**
   * Submits a step to move on.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response.
   */
  public function submitStep(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    // Display errors in form, if any.
    if ($form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#uswds_grid-dialog-form', $form));
    }
    else {
      $form_state->set('step', $form_state->get('step') === 'select' ? 'layout' : 'advanced');
      $form_state->setRebuild();
      $rebuild_form = $this->formBuilder->rebuildForm('uswds_ckeditor_integration_dialog', $form_state, $form);
      unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
      $response->addCommand(new HtmlCommand('#uswds_grid-dialog-form', $rebuild_form));
      $response->addCommand(new SetDialogTitleCommand('', $rebuild_form['#title']));
    }
    return $response;
  }

  /**
   * Submits a backstep. @todo this should probably just go above.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response.
   */
  public function submitBackStep(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $form_state->set('step', $form_state->get('step') === 'advanced' ? 'layout' : 'select');
    $form_state->setRebuild();
    $rebuild_form = $this->formBuilder->rebuildForm('uswds_ckeditor_integration_dialog', $form_state, $form);
    unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
    $response->addCommand(new HtmlCommand('#uswds_grid-dialog-form', $rebuild_form));
    $response->addCommand(new SetDialogTitleCommand('', $rebuild_form['#title']));

    return $response;
  }

  /**
   * Commit the changes and close the dialog window.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response.
   */
  public function submitDialog(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $settings = $form_state->get('uswds_grid_settings');

    // Set container classes.
    if (isset($settings['add_container']) && isset($settings['container_class'])) {
      $settings['container_class'] = 'grid-container ' . $settings['container_class'];
    }
    elseif (isset($settings['add_container']) && !isset($settings['container_class'])) {
      $settings['container_class'] = 'grid-container';
    }
    else {
      $settings['container_class'] = '';
    }

    // Set row classes.
    if (isset($settings['row_class'])) {
      if (!str_contains($settings['row_class'], 'grid-row')) {
        $settings['row_class'] = 'grid-row ' . $settings['row_class'];
      }
      if ($settings['no_gutter'] && !str_contains($settings['row_class'], 'no-gutters')) {
        $settings['row_class'] = 'no-gutters ' . $settings['row_class'];
      }
    }
    elseif ($settings['no_gutter']) {
      $settings['row_class'] = 'grid-row no-gutters';
    }
    else {
      $settings['row_class'] = 'grid-row grid-gap';
    }

    // Parse out the column classes.
    for ($i = 1; $i <= $settings['num_columns']; $i++) {
      $keys = [];
      $col = 'col_' . $i . '_classes';
      $original = explode(' ', $settings[$col]);
      foreach ($original as $key => $item) {
        if (str_contains($item, ':')) {
          unset($original[$key]);
        }
      }
      foreach ($settings['breakpoints'] as $prefix => $selection) {
        unset($settings[$col]);
        // Advanced.
        if ($selection['layout'] === 'none') {
          continue;
        }
        else {
          $vals = explode('_', $selection['layout']);
          $col_value = $vals[$i - 1];
          if (!empty($col_value)) {
            $suffix = $col_value === 'equal' ? '' : '-' . $col_value;
            if ($prefix === 'none') {
              $keys['col' . $suffix] = TRUE;
            }
            else {
              $keys[$prefix . ':grid-col' . $suffix] = TRUE;
            }
          }
        }
      }

      $diff = array_unique(array_merge(array_keys($keys), $original));
      $diff = implode(' ', $diff);
      if (!empty($settings[$col])) {
        $settings[$col] = $diff . ' ' . $settings[$col];
      }
      else {
        $settings[$col] = $diff;
      }
    }

    // Track that we're committed.
    $settings['saved'] = TRUE;
    $values = ['settings' => $settings];

    $response->addCommand(new EditorDialogSave($values));
    $response->addCommand(new CloseModalDialogCommand());

    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing needed here.
  }

}
