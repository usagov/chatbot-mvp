import { Plugin } from 'ckeditor5/src/core';
import { toWidget, toWidgetEditable, Widget } from 'ckeditor5/src/widget';
import InsertAccordionCommand from './insertaccordioncommand';
import InsertAccordionRowCommand from './insertaccordionrowcommand';
import DeleteAccordionRowCommand from './deleteaccordionrowcommand';

// cSpell:ignore accordion insertsimpleboxcommand

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to HTML that
 * is inserted in the DOM.
 *
 * CKEditor 5 internally interacts with accordion as this model:
 * <accordion>
 *    <accordionTitle></accordionTitle>
 *    <accordionContent></accordionContent>
 * </accordion>
 *
 * This file has the logic for defining the accordion model, and for how it is
 * converted to standard DOM markup.
 */
export default class AccordionEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'AccordionEditing';
  }

  static get requires() {
    return [Widget];
  }

  init() {
    this._defineSchema();
    this._defineConverters();
    this.editor.commands.add(
      'insertAccordion',
      new InsertAccordionCommand(this.editor),
    );

    this.editor.commands.add(
      'insertAccordionRowAbove',
      new InsertAccordionRowCommand(this.editor, { order: 'above' }),
    );
    this.editor.commands.add(
      'insertAccordionRowBelow',
      new InsertAccordionRowCommand(this.editor, { order: 'below' }),
    );
    this.editor.commands.add(
      'deleteAccordionRow',
      new DeleteAccordionRowCommand(this.editor, {}),
    );
  }

  /*
   * This registers the structure that will be seen by CKEditor 5 as
   * <accordion>
   *    <accordionTitle></accordionTitle>
   *    <accordionContent></accordionContent>
   * </accordion>
   *
   * The logic in _defineConverters() will determine how this is converted to
   * markup.
   */
  _defineSchema() {
    // Schemas are registered via the central `editor` object.
    const { schema } = this.editor.model;

    schema.register('accordion', {
      // Behaves like a self-contained object (e.g. an image).
      isObject: true,
      // Allow in places where other blocks are allowed (e.g. directly in the root).
      allowWhere: '$block',
    });

    schema.register('accordionRow', {
      allowIn: 'accordion',
      isLimit: true,
    });

    schema.register('accordionTitle', {
      isLimit: true,
      allowIn: 'accordion',
      allowContentOf: '$block',
    });

    schema.register('accordionContent', {
      isLimit: true,
      allowIn: 'accordion',
      allowContentOf: '$root',
    });

    schema.addChildCheck((context, childDefinition) => {
      // Disallow accordion inside accordionContent.
      if (
        (context.endsWith('accordionContent') ||
          context.endsWith('accordionTitle')) &&
        childDefinition.name === 'accordion'
      ) {
        return false;
      }
    });
  }

  /**
   * Converters determine how CKEditor 5 models are converted into markup and
   * vice-versa.
   */
  _defineConverters() {
    // Converters are registered via the central editor object.
    const { conversion } = this.editor;

    const { options } = this.editor.config.get('uswdsAccordionConfig');
    let $defaultBordered = false;
    let $accordionClass = 'usa-accordion';

    if (options) {
      $defaultBordered = options.bordered;

      if ($defaultBordered) {
        $accordionClass += ' usa-accordion--bordered';
      }
    }

    // Upcast Converters: determine how existing HTML is interpreted by the
    // editor. These trigger when an editor instance loads.
    conversion.for('upcast').elementToElement({
      model: 'accordion',
      view: {
        name: 'div',
        classes: 'usa-accordion',
      },
    });

    conversion.for('upcast').add((dispatcher) => {
      // Look for every view div element.
      dispatcher.on('element:h4', (evt, data, conversionApi) => {
        // Get all the necessary items from the conversion API object.
        const {
          consumable,
          writer,
          safeInsert,
          convertChildren,
          updateConversionResult,
        } = conversionApi;

        // Get view item from data object.
        const { viewItem } = data;

        // Define elements consumables.
        const wrapper = { name: true, classes: 'usa-accordion__heading' };
        const innerWrapper = { name: true, classes: 'usa-accordion__button' };

        // Tests if the view element can be consumed.
        if (!consumable.test(viewItem, wrapper)) {
          return;
        }

        // Check if there is only one child.
        if (viewItem.childCount !== 1) {
          return;
        }

        // Get the first child element.
        const firstChildItem = viewItem.getChild(0);

        // Check if the first element is a div.
        if (!firstChildItem.is('element', 'div')) {
          return;
        }

        // Tests if the first child element can be consumed.
        if (!consumable.test(firstChildItem, innerWrapper)) {
          return;
        }

        // Create model element.
        const modelElement = writer.createElement('accordionTitle');

        // Insert element on a current cursor location.
        if (!safeInsert(modelElement, data.modelCursor)) {
          return;
        }

        // Consume the main outer wrapper element.
        consumable.consume(viewItem, wrapper);
        // Consume the inner wrapper element.
        consumable.consume(firstChildItem, innerWrapper);

        // Handle children conversion inside inner wrapper element.
        convertChildren(firstChildItem, modelElement);

        // Necessary function call to help setting model range and cursor
        // for some specific cases when elements being split.
        updateConversionResult(modelElement, data);
      });
    });

    conversion.for('upcast').elementToElement({
      model: 'accordionContent',
      view: {
        name: 'div',
        classes: 'usa-accordion__content',
      },
    });

    // Data Downcast Converters: converts stored model data into HTML.
    // These trigger when content is saved.
    conversion.for('dataDowncast').elementToElement({
      model: 'accordion',
      view: {
        name: 'div',
        classes: $accordionClass,
      },
    });

    conversion.for('dataDowncast').add((dispatcher) => {
      dispatcher.on('insert:accordionTitle', (evt, data, conversionApi) => {
        // Remember to check whether the change has not been consumed yet and consume it.
        if (!conversionApi.consumable.consume(data.item, 'insert')) {
          return;
        }
        const { writer, mapper } = conversionApi;

        // Translate the position in the model to a position in the view.
        const viewPosition = mapper.toViewPosition(data.range.start);

        // Create a <div> element that will be inserted into the view at the `viewPosition`.
        const h4 = writer.createContainerElement('h4', {
          class: 'usa-accordion__heading',
        });

        const buttonDiv = writer.createEditableElement('div', {
          class: 'usa-accordion__button',
          type: 'button',
          'aria-expanded': false,
        });
        writer.insert(writer.createPositionAt(h4, 0), buttonDiv);

        // Bind the newly created view element to the model element so
        // positions will map accordingly in the future.
        mapper.bindElements(data.item, buttonDiv);

        // Add the newly created view element to the view.
        writer.insert(viewPosition, h4);

        // Remember to stop the event propagation.
      });
    });

    conversion.for('dataDowncast').elementToElement({
      model: 'accordionContent',
      view: (modelElement, { writer }) =>
        writer.createContainerElement('div', {
          class: 'usa-accordion__content usa-prose',
        }),
    });

    // Editing Downcast Converters. These render the content to the user for
    // editing, i.e. this determines what gets seen in the editor. These trigger
    // after the Data Upcast Converters, and are re-triggered any time there
    // are changes to any of the models' properties.
    //
    // Convert the <accordion> model into a container widget in the editor UI.
    conversion.for('editingDowncast').elementToElement({
      model: 'accordion',
      view: (modelElement, { writer: viewWriter }) => {
        const div = viewWriter.createContainerElement('div', {
          class: $accordionClass,
        });

        return toWidget(div, viewWriter);
      },
    });

    // Convert the <accordionTitle> model into an editable <div> widget.
    conversion.for('editingDowncast').elementToElement({
      model: 'accordionTitle',
      view: (modelElement, { writer: viewWriter }) => {
        const div = viewWriter.createEditableElement('div', {
          class: 'ckeditor-accordion-title',
        });
        return toWidgetEditable(div, viewWriter);
      },
    });

    // Convert the <accordionContent> model into an editable <div> widget.
    conversion.for('editingDowncast').elementToElement({
      model: 'accordionContent',
      view: (modelElement, { writer: viewWriter }) => {
        const div = viewWriter.createEditableElement('div', {
          class: 'ckeditor-accordion-content',
        });
        return toWidgetEditable(div, viewWriter);
      },
    });
  }
}
