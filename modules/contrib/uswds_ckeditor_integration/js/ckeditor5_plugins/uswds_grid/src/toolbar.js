import { Plugin, icons } from 'ckeditor5/src/core';
import { WidgetToolbarRepository } from 'ckeditor5/src/widget';
import { ButtonView } from 'ckeditor5/src/ui';
import {
  getClosestSelectedUswdsGridWidget,
  convertGridToSettings,
} from './utils';

/**
 * @private
 */
export default class UswdsGridToolbar extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [WidgetToolbarRepository];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'UswdsGridToolbar';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const options = editor.config.get('uswdsGrid');
    if (!options) {
      return;
    }

    const { dialogURL, openDialog, dialogSettings = {} } = options;

    if (!dialogURL || typeof openDialog !== 'function') {
      return;
    }

    editor.ui.componentFactory.add('uswdsGridEdit', (locale) => {
      const buttonView = new ButtonView(locale);

      buttonView.set({
        label: editor.t('Edit Grid'),
        icon: icons.pencil,
        tooltip: true,
        withText: true,
      });

      this.listenTo(buttonView, 'execute', () => {
        let existingValues = {};

        const { selection } = editor.editing.view.document;
        const selectedGrid = getClosestSelectedUswdsGridWidget(selection);

        if (selectedGrid) {
          existingValues = convertGridToSettings(selectedGrid);
          existingValues.saved = 1;
        }

        // Open a dialog to select entity to embed.
        this._openDialog(
          dialogURL,
          existingValues,
          ({ settings }) => {
            editor.execute('insertUswdsGrid', settings);
            editor.editing.view.focus();
          },
          dialogSettings,
        );
      });

      return buttonView;
    });
  }

  /**
   * @inheritdoc
   */
  afterInit() {
    const { editor } = this;
    const widgetToolbarRepository = editor.plugins.get(
      'WidgetToolbarRepository',
    );

    widgetToolbarRepository.register('uswdsGrid', {
      items: ['uswdsGridEdit'],
      // Get the selected grid with the selection inside.
      getRelatedElement: (selection) =>
        getClosestSelectedUswdsGridWidget(selection),
    });
  }

  /**
   * Helper until https://www.drupal.org/project/drupal/issues/3303191
   *
   * @param {string} url
   *   The URL that contains the contents of the dialog.
   * @param {object} existingValues
   *   Existing values that will be sent via POST to the url for the dialog
   *   contents.
   * @param {function} saveCallback
   *   A function to be called upon saving the dialog.
   * @param {object} dialogSettings
   *   An object containing settings to be passed to the jQuery UI.
   */
  _openDialog(url, existingValues, saveCallback, dialogSettings) {
    // Add a consistent dialog class.
    const classes = dialogSettings.dialogClass
      ? dialogSettings.dialogClass.split(' ')
      : [];
    classes.push('ui-dialog--narrow');
    dialogSettings.dialogClass = classes.join(' ');
    dialogSettings.autoResize = window.matchMedia('(min-width: 600px)').matches;
    dialogSettings.width = 'auto';

    const ckeditorAjaxDialog = Drupal.ajax({
      dialog: dialogSettings,
      dialogType: 'modal',
      selector: '.ckeditor5-dialog-loading-link',
      url,
      progress: { type: 'fullscreen' },
      submit: {
        editor_object: existingValues,
      },
    });
    ckeditorAjaxDialog.execute();

    // Store the save callback to be executed when this dialog is closed.
    Drupal.ckeditor5.saveCallback = saveCallback;
  }
}
