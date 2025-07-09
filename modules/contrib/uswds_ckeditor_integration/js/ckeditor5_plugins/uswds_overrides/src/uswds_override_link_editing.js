import { Plugin } from 'ckeditor5/src/core';
import { Widget } from 'ckeditor5/src/widget';

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to markup that
 * is inserted in the DOM.
 */
export default class UswdsOverrideLinkEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UswdsOverrideLinkEditing';
  }

  static get requires() {
    return [Widget];
  }

  init() {
    const { editor } = this;
    const options = this.editor.config.get('uswds.options');
    let linksEnabled = false;

    if (options) {
      linksEnabled = !!options[0].override_links;
    }

    if (linksEnabled) {
      editor.model.schema.extend('$text', { allowAttributes: 'linkClass' });

      editor.conversion.for('downcast').add((dispatcher) => {
        dispatcher.on(
          'attribute:linkHref',
          (evt, data, conversionApi) => {
            const viewWriter = conversionApi.writer;
            const viewSelection = viewWriter.document.selection;
            const viewElement = viewWriter.createAttributeElement(
              'a',
              {
                class: 'usa-link',
              },
              {
                priority: 5,
              },
            );

            if (data.item.is('selection')) {
              viewWriter.wrap(viewSelection.getFirstRange(), viewElement);
            }
            else {
              viewWriter.wrap(
                conversionApi.mapper.toViewRange(data.range),
                viewElement,
              );
            }
          },
          { priority: 'low' },
        );
      });
    }
  }
}
