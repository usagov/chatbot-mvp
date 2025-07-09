/**
 * @file registers the paragraphsEmbedUI toolbar button and binds functionality
 *   to it.
 */

import { Plugin } from 'ckeditor5/src/core';
import { ButtonView, ContextualBalloon } from 'ckeditor5/src/ui';
import icon from '../../../../icons/paragraph.svg';
import { DomEventObserver } from "ckeditor5/src/engine";

/**
 * Ckeditor5 doesn't support double click out of the box.
 * Register it here so we can use it.
 *
 * @Todo Replace double click with a balloon style popup menu to
 *   edit the embedded content item.
 */
class DoubleClickObserver extends DomEventObserver {
  constructor(view) {
    super(view);
    this.domEventType = 'dblclick';
  }

  onDomEvent(domEvent) {
    this.fire(domEvent.type, domEvent);
  }
}

export default class ParagraphsEmbedUI extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [ContextualBalloon];
  }

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const command = editor.commands.get('insertParagraphEmbed');
    const options = editor.config.get('embeddedParagraph');

    if (!options) {
      return;
    }

    const embed_buttons = options.buttons;
    const { dialogSettings = {} } = options;

    // Register each embed button to the toolbar based on configuration.
    Object.keys(embed_buttons).forEach((id, index) => {
      // Add each button to the toolbar.
      editor.ui.componentFactory.add(id, (locale) => {
        const button = embed_buttons[id];
        const buttonView = new ButtonView(locale);

        // Create the toolbar button.
        buttonView.set({
          isEnabled: true,
          label: button.label,
          icon: icon,
          tooltip: true,
        });
        buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');

        this.listenTo(buttonView, 'execute', () => {
          const libraryURL = this._getCurrentLibraryUrl(options) || Drupal.url('paragraph-embed/dialog/' + options.format + '/' + id);

          // Open a dialog to select entity to embed.
          Drupal.ckeditor5.openDialog(
            libraryURL,
            ({ attributes }) => {
              editor.execute('insertParagraphEmbed', attributes);
            },
            dialogSettings,
          );
        });

        return buttonView;
      });
    });

    const view = editor.editing.view;
    const viewDocument = view.document;

    view.addObserver(DoubleClickObserver);

    editor.listenTo(viewDocument, 'dblclick', (evt, data) => {
      const libraryURL = this._getCurrentLibraryUrl(options);

      if (libraryURL) {
        // Open a dialog to edit paragraph.
        Drupal.ckeditor5.openDialog(
          libraryURL,
          ({ attributes }) => {
            editor.execute('insertParagraphEmbed', attributes);
          },
          dialogSettings,
        );
      }
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'ParagraphsEmbedUI';
  }

  /**
   * Get the library url based on the current selection.
   *
   * @param {object} options
   *   The configuration options.
   *
   * @returns {string|undefined}
   *   The library url or undefined if no selection.
   */
  _getCurrentLibraryUrl(options) {
    const editor = this.editor;
    const view = editor.editing.view;
    const selection = view.document.selection;
    const selectedElement = selection.getSelectedElement();
    const modelElement = editor.editing.mapper.toModelElement(selectedElement);

    if (modelElement
      && typeof modelElement.name !== 'undefined'
      && modelElement.name === 'embeddedParagraph') {
      const paragraphId = modelElement.getAttribute('embeddedParagraphId');
      const paragraphRevisionId = modelElement.getAttribute('embeddedParagraphRevisionId');
      const paragraphEmbedButton = modelElement.getAttribute('embeddedParagraphPluginType');

      let libraryURL = Drupal.url('paragraph-embed/dialog/' + options.format + '/' + paragraphEmbedButton);

      if (paragraphId) {
        libraryURL += '/' + paragraphId;
      }
      if (paragraphRevisionId) {
        libraryURL += '/' + paragraphRevisionId;
      }

      return libraryURL;
    }

  }

}
