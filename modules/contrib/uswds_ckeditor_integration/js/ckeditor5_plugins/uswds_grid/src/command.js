import { Command } from 'ckeditor5/src/core';
import { getClosestSelectedUswdsGridElement } from './utils';

/**
 * Creates a new USWDS Grid
 *
 * @param {module:engine/model/writer~Writer} writer
 *   The model writer.
 * @param {{}} settings
 *   The settings
 * @return {*}
 *   The grid.
 */
function createUswdsGrid(writer, settings) {
  // Grid.
  const gridAttributes = { class: 'uswds_grid' };
  if (typeof settings.container_wrapper_class !== "undefined") {
    gridAttributes.class += ` ${settings.container_wrapper_class}`;
  }
  if (settings.add_container) {
    gridAttributes.class += ` grid-container`;
  }

  // Create the grid.
  const uswdsGrid = writer.createElement('uswdsGrid', gridAttributes);

  // Row.
  const rowAttributes = {
    class: settings.row_class.trim(),
    'data-row-none': settings.breakpoints.none
      ? settings.breakpoints.none.layout
      : '',
    'data-row-sm': settings.breakpoints.sm
      ? settings.breakpoints.sm.layout
      : '',
    'data-row-md': settings.breakpoints.md
      ? settings.breakpoints.md.layout
      : '',
    'data-row-lg': settings.breakpoints.lg
      ? settings.breakpoints.lg.layout
      : '',
    'data-row-xl': settings.breakpoints.xl
      ? settings.breakpoints.xl.layout
      : '',
    'data-row-xxl': settings.breakpoints.xxl
      ? settings.breakpoints.xxl.layout
      : '',
  };
  const uswdsGridRow = writer.createElement('uswdsGridRow', rowAttributes);

  // Cols.
  for (let i = 1; i <= settings.num_columns; i++) {
    const key = `col_${i}_classes`;
    const uswdsGridCol = writer.createElement('uswdsGridCol', {
      class: settings[key],
    });
    const colcontent = writer.createElement('paragraph');
    writer.insertText(`Column ${i} content`, colcontent);
    writer.append(colcontent, uswdsGridCol);
    writer.append(uswdsGridCol, uswdsGridRow);
  }

  writer.append(uswdsGridRow, uswdsGrid);

  return uswdsGrid;
}

/**
 * Updates an existing USWDS Grid
 *
 * @param {module:engine/model/writer~Writer} writer
 *   The model writer.
 * @param {module:engine/view/element~Element|null} existingGrid
 * @param {{}} settings
 *   The settings
 */
function updateExisting(writer, existingGrid, settings) {
  let row;

  // Grid.
  const gridAttributes = { class: 'uswds_grid' };
  if (settings.container_wrapper_class !== 'undefined') {
    gridAttributes.class += ` ${settings.container_wrapper_class}`;
  }
  gridAttributes.class = gridAttributes.class.trim();
  writer.setAttributes(gridAttributes, existingGrid);

  // First child might be container or row.
  row = existingGrid.getChild(0);

  // Row update.
  const rowAttributes = {
    class: settings.row_class.trim(),
    'data-row-none': settings.breakpoints.none
      ? settings.breakpoints.none.layout
      : '',
    'data-row-sm': settings.breakpoints.sm
      ? settings.breakpoints.sm.layout
      : '',
    'data-row-md': settings.breakpoints.md
      ? settings.breakpoints.md.layout
      : '',
    'data-row-lg': settings.breakpoints.lg
      ? settings.breakpoints.lg.layout
      : '',
    'data-row-xl': settings.breakpoints.xl
      ? settings.breakpoints.xl.layout
      : '',
    'data-row-xxl': settings.breakpoints.xxl
      ? settings.breakpoints.xxl.layout
      : '',
  };
  writer.setAttributes(rowAttributes, row);

  // Cols.
  for (let i = 1; i <= settings.num_columns; i++) {
    const key = `col_${i}_classes`;
    writer.setAttributes({ class: settings[key] }, row.getChild(i - 1));
  }
}

/**
 * Inserts a grid or updates a new one.
 */
export default class InsertUswdsGridCommand extends Command {
  execute(settings) {
    const { model } = this.editor;
    const existingGrid = getClosestSelectedUswdsGridElement(
      model.document.selection,
    );

    model.change((writer) => {
      if (existingGrid) {
        updateExisting(writer, existingGrid, settings);
      }
      else {
        model.insertContent(createUswdsGrid(writer, settings));
      }
    });
  }

  refresh() {
    const { model } = this.editor;
    const { selection } = model.document;
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'uswdsGrid',
    );
    this.isEnabled = allowedIn !== null;
  }
}
