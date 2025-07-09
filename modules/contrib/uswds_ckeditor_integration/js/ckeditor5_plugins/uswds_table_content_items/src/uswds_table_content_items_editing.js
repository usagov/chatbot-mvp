import { Plugin } from 'ckeditor5/src/core';
import SetTableClass from './commands/setTableClass';

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to HTML that
 * is inserted in the DOM.
 *
 * This file has the logic for defining the simpleBox model, and for how it is
 * converted to standard DOM markup.
 */
export default class UswdsTableContentItemsEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UswdsTableContentItemsEditing';
  }

  /**
   * @inheritDoc
   */
  init() {
    const { editor } = this;
    const { model } = editor;

    const uswdsClasses = [
      { id: 'borderless', classes: 'usa-table--borderless' },
      { id: 'scrollable', classes: 'usa-table--scrollable' },
      { id: 'stacked', classes: 'usa-table--stacked' },
      { id: 'sortable', classes: 'usa-table--sortable' },
      { id: 'striped', classes: 'usa-table--striped' },
    ];

    model.schema.extend('table', {
      allowAttributes: [
        'borderless',
        'scrollable',
        'stacked',
        'sortable',
        'class',
      ],
    });

    // Define all the commands.
    editor.commands.add(
      'setBorderlessClass',
      new SetTableClass(editor, 'borderless'),
    );
    editor.commands.add(
      'setScrollableClass',
      new SetTableClass(editor, 'scrollable'),
    );
    editor.commands.add(
      'setStackedClass',
      new SetTableClass(editor, 'stacked'),
    );
    editor.commands.add(
      'setSortableClass',
      new SetTableClass(editor, 'sortable'),
    );
    editor.commands.add(
      'setStripedClass',
      new SetTableClass(editor, 'striped'),
    );

    uswdsClasses.forEach((tableClass) => {
      editor.model.schema.extend('table', { allowAttributes: tableClass.id });

      editor.conversion.for('upcast').attributeToAttribute({
        model: {
          name: 'table',
          key: tableClass.id,
          value: true,
        },
        view: {
          key: 'class',
          value: tableClass.classes,
        },
      });

      const val = `attribute:${tableClass.id}:table`;

      // Apply attribute to table element no matter if it's needed or not.
      editor.conversion.for('downcast').add((dispatcher) => {
        dispatcher.on(val, (evt, data, conversionApi) => {
          const viewElement = conversionApi.mapper.toViewElement(data.item);
          conversionApi.writer.addClass(tableClass.classes, viewElement);
        });
      });

      if (tableClass.id === 'scrollable') {
        editor.conversion.for('downcast').add((dispatcher) => {
          dispatcher.on(val, (evt, data, conversionApi) => {
            if (data.item.name === 'table') {
              const viewElement = conversionApi.mapper.toViewElement(data.item);
              const { writer, mapper } = conversionApi;

              const parentName = data.item.parent.name;
              let alreadyWrapped = false;
              if (parentName === 'htmlDiv') {
                // eslint-disable-next-line max-nested-callbacks
                data.item.parent._attrs.forEach((values) => {
                  if (Array.isArray(values.classes)) {
                    // eslint-disable-next-line guard-for-in
                    for (const i in values.classes) {
                      const className = values.classes[i];
                      if (className === 'usa-table-container--scrollable') {
                        alreadyWrapped = true;
                        return;
                      }
                    }
                  }
                });
              }

              if (alreadyWrapped) {
                return;
              }

              // Translate the position in the model to a position in the view.
              const viewPosition = mapper.toViewPosition(data.range.start);

              // Create a <div> element that will be inserted into the view at
              // the `viewPosition`.
              const div = writer.createContainerElement('div', {
                class: 'usa-table-container--scrollable',
              });

              // Create the <span> element that will be inserted into the div
              writer.insert(writer.createPositionAt(div, 0), viewElement);

              // Add the newly created view element to the view.
              writer.insert(viewPosition, div);

              // Remember to stop the event propagation.
              evt.stop();
            }
          });
        });
      }
    });
  }
}
