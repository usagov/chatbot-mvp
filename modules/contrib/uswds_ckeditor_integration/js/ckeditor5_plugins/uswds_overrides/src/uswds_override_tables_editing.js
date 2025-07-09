import { Plugin } from 'ckeditor5/src/core';
import { Widget } from 'ckeditor5/src/widget';

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to markup that
 * is inserted in the DOM.
 */
export default class UswdsOverridesEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UswdsOverridesEditing';
  }

  static get requires() {
    return [Widget];
  }

  init() {
    const { editor } = this;
    const options = this.editor.config.get('uswds.options');
    let tablesDefaultEnabled = false;

    if (options) {
      tablesDefaultEnabled = !!options[0].override_tables;
    }

    if (tablesDefaultEnabled) {
      editor.conversion.for('downcast').add((dispatcher) => {
        dispatcher.on(
          'insert:table',
          (evt, data, conversionApi) => {
            const viewWriter = conversionApi.writer;

            if (data.item.name === 'table') {
              viewWriter.addClass(
                'usa-table',
                conversionApi.mapper.toViewElement(data.item),
              );
            }
          },
          { priority: 'low' },
        );
      });
    }
  }
}
