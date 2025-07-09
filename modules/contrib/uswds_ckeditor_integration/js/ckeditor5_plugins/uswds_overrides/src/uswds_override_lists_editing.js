import { Plugin } from 'ckeditor5/src/core';
import { Widget } from 'ckeditor5/src/widget';

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to markup that
 * is inserted in the DOM.
 */
export default class UswdsOverrideListsEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UswdsOverrideListsEditing';
  }

  static get requires() {
    return [Widget];
  }

  init() {
    const { editor } = this;
    const options = this.editor.config.get('uswds.options');
    let linksEnabled = false;

    if (options) {
      linksEnabled = !!options[0].override_lists;
    }

    if (linksEnabled) {
      // Add usa-list to <ol> and <ul> tags.
      editor.conversion.for('dataDowncast').elementToElement({
        model: 'paragraph',
        view: (modelElement, { writer }) => {
          if (modelElement._attrs.has('listType')) {
            const attributes = modelElement._attrs.get('htmlListAttributes');
            let classes = '';

            if (
              !attributes ||
              (attributes && !attributes.classes.includes('usa-list'))
            ) {
              classes = 'usa-list';
            }

            if (attributes && attributes.hasOwnProperty('classes')) {
              classes += ` ${attributes.classes}`;
            }

            modelElement._attrs.set('htmlListAttributes', {
              classes: classes.trim(),
            });
          }
        },
        converterPriority: 'highest',
      });
    }
  }
}
