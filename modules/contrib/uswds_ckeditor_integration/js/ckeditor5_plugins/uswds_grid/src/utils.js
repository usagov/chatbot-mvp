import { isWidget } from 'ckeditor5/src/widget';

/**
 * Checks if the provided model element is `uswdsGrid`.
 *
 * @param {module:engine/model/element~Element} modelElement
 *   The model element to be checked.
 * @return {boolean}
 *   A boolean indicating if the element is a uswdsGrid element.
 *
 * @private
 */
export function isUswdsGrid(modelElement) {
  return !!modelElement && modelElement.is('element', 'uswdsGrid');
}

/**
 * Checks if view element is <uswdsGrid> element.
 *
 * @param {module:engine/view/element~Element} viewElement
 *   The view element.
 * @return {boolean}
 *   A boolean indicating if the element is a <uswdsGrid> element.
 *
 * @private
 */
export function isUswdsGridWidget(viewElement) {
  return isWidget(viewElement) && !!viewElement.getCustomProperty('uswdsGrid');
}

/**
 * Checks if view element is <uswdsGridRow> element.
 *
 * @param {module:engine/view/element~Element} viewElement
 *   The view element.
 * @return {boolean}
 *   A boolean indicating if the element is a <uswdsGridRow> element.
 *
 * @private
 */
export function isUswdsGridRowWidget(viewElement) {
  return (
    isWidget(viewElement) && !!viewElement.getCustomProperty('uswdsGridRow')
  );
}

/**
 * Checks if view element is <uswdsGridCol> element.
 *
 * @param {module:engine/view/element~Element} viewElement
 *   The view element.
 * @return {boolean}
 *   A boolean indicating if the element is a <uswdsGridCol> element.
 *
 * @private
 */
export function isUswdsGridColWidget(viewElement) {
  return !!viewElement.getCustomProperty('uswdsGridCol');
}

/**
 * Gets `uswdsGrid` element from selection.
 *
 * @param {module:engine/model/selection~Selection|module:engine/model/documentselection~DocumentSelection} selection
 *   The current selection.
 * @return {module:engine/model/element~Element|null}
 *   The `uswdsGrid` element which could be either the current selected an
 *   ancestor of the selection. Returns null if the selection has no Grid
 *   element.
 *
 * @private
 */
export function getClosestSelectedUswdsGridElement(selection) {
  const selectedElement = selection.getSelectedElement();

  return isUswdsGrid(selectedElement)
    ? selectedElement
    : selection.getFirstPosition().findAncestor('uswdsGrid');
}

/**
 * Gets selected UswdsGrid widget if only UswdsGrid is currently selected.
 *
 * @param {module:engine/model/selection~Selection} selection
 *   The current selection.
 * @return {module:engine/view/element~Element|null}
 *   The currently selected Grid widget or null.
 *
 * @private
 */
export function getClosestSelectedUswdsGridWidget(selection) {
  const viewElement = selection.getSelectedElement();
  if (viewElement && isUswdsGridWidget(viewElement)) {
    return viewElement;
  }

  // Nothing Selected.
  if (selection.getFirstPosition() === null) {
    return null;
  }

  let { parent } = selection.getFirstPosition();
  while (parent) {
    if (parent.is('element') && isUswdsGridWidget(parent)) {
      return parent;
    }
    parent = parent.parent;
  }
  return null;
}

/**
 * Extracts classes for settings.
 *
 * @param {*} element
 *   The element being passed.
 * @param {string} base
 *   The base class to exclude.
 * @param {boolean} reverse
 *   Whether to reverse the affect.
 * @return {string|string|string}
 *   The class list.
 */
export function extractGridClasses(element, base, reverse = false) {
  reverse = reverse || false;
  let classes = '';

  if (typeof element.getAttribute === 'function') {
    classes = element.getAttribute('class');
  }
 else if (typeof element.className === 'string') {
    classes = element.className;
  }

  // Failsafe.
  if (!classes) {
    return '';
  }

  const classlist = classes.split(' ').filter((c) => {
    if (
      c.lastIndexOf('ck-widget', 0) === 0 ||
      c.lastIndexOf('ck-edit', 0) === 0 ||
      c.lastIndexOf('uswdsg-', 0) === 0
    ) {
      return false;
    }
    return reverse
      ? c.lastIndexOf(base, 0) === 0
      : c.lastIndexOf(base, 0) !== 0;
  });

  return classlist.length ? classlist.join(' ').trim() : '';
}

/**
 * Converts a grid into a settings object.
 *
 * @param {module:engine/view/element~Element|null} grid
 *   The current grid.
 * @return {{}}
 *   The settings.
 */
export function convertGridToSettings(grid) {
  const settings = {};
  let row = false;
  let gridClasses = extractGridClasses(grid, 'uswds_grid');
  settings.container_wrapper_class = gridClasses;

  // First child might be container or row.
  const firstChild = grid.getChild(0);

  if (gridClasses.includes('grid-container')) {
    settings.add_container = 1;
  }
 else {
    settings.add_container = 0;
  }

  row = firstChild;

  // Row options.
  const rowClasses = extractGridClasses(row, 'row');
  settings.no_gutter = rowClasses.indexOf('no-gutters') !== -1 ? 1 : 0;
  settings.row_class = rowClasses.replace('no-gutters', '').replace('g-0', '');

  // Layouts.
  settings.breakpoints = {
    none: { layout: row.getAttribute('data-row-none') },
    sm: { layout: row.getAttribute('data-row-sm') },
    md: { layout: row.getAttribute('data-row-md') },
    lg: { layout: row.getAttribute('data-row-lg') },
    xl: { layout: row.getAttribute('data-row-xl') },
    xxl: { layout: row.getAttribute('data-row-xxl') },
  };

  // Col options.
  settings.num_columns = 0;
  Array.from(row.getChildren()).forEach((col, idx) => {
    if (isUswdsGridColWidget(col)) {
      settings.num_columns += 1;
      const colClass = extractGridClasses(col, 'col');
      const key = `col_${idx + 1}_classes`;
      settings[key] = colClass;
    }
  });

  return settings;
}
