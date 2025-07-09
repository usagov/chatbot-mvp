/**
 * @file registers the simpleBox toolbar button and binds functionality to it.
 */

import { Plugin } from 'ckeditor5/src/core';
import {
  addListToDropdown,
  createDropdown,
  ViewModel,
  SwitchButtonView,
} from 'ckeditor5/src/ui';
import { Collection } from 'ckeditor5/src/utils';

import tableColumnIcon from '../../../../images/icons/tables/star.svg';

export default class UswdsTableContentItemsUi extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UswdsTableContentItemsUi';
  }

  /**
   * @inheritDoc
   */
  init() {
    const { editor } = this;
    const { t } = this.editor;

    editor.ui.componentFactory.add('tableUswds', (locale) => {
      const options = [
        {
          type: 'switchbutton',
          model: {
            commandName: 'setBorderlessClass',
            label: t('Borderless'),
            bindIsOn: true,
          },
        },
        { type: 'separator' },
        {
          type: 'switchbutton',
          model: {
            commandName: 'setScrollableClass',
            label: t('Scrollable'),
            bindIsOn: true,
          },
        },
        { type: 'separator' },
        {
          type: 'switchbutton',
          model: {
            commandName: 'setStackedClass',
            label: t('Stacked'),
            bindIsOn: true,
          },
        },
        { type: 'separator' },
        {
          type: 'switchbutton',
          model: {
            commandName: 'setSortableClass',
            label: t('Sortable'),
            bindIsOn: true,
          },
        },
        { type: 'separator' },
        {
          type: 'switchbutton',
          model: {
            commandName: 'setStripedClass',
            label: t('Striped'),
            bindIsOn: true,
          },
        },
      ];

      return this._prepareDropdown(
        t('USWDS Classes'),
        tableColumnIcon,
        options,
        locale,
      );
    });
  }

  /**
   * Creates a dropdown view from a set of options.
   *
   * @private
   * @param {String} label The dropdown button label.
   * @param {{}} icon An icon for the dropdown button.
   * @param {Array.<module:ui/dropdown/utils~ListDropdownItemDefinition>} options The list of options for the dropdown.
   * @param {module:utils/locale~Locale} locale
   * @return {module:ui/dropdown/dropdownview~DropdownView}
   */
  _prepareDropdown(label, icon, options, locale) {
    const { editor } = this;
    const dropdownView = createDropdown(locale);
    const commands = this._fillDropdownWithListOptions(dropdownView, options);

    // Decorate dropdown's button.
    dropdownView.buttonView.set({
      label,
      icon,
      tooltip: true,
    });

    // Make dropdown button disabled when all options are disabled.
    dropdownView
      .bind('isEnabled')
      .toMany(commands, 'isEnabled', (...areEnabled) =>
        areEnabled.some((isEnabled) => isEnabled),
      );

    this.listenTo(dropdownView, 'execute', (evt) => {
      editor.execute(evt.source.commandName);

      // Toggling a switch button view should not move the focus to the editable.
      if (!(evt.source instanceof SwitchButtonView)) {
        editor.editing.view.focus();
      }
    });

    return dropdownView;
  }

  /**
   * Injects a {@link module:ui/list/listview~ListView} into the passed dropdown with buttons
   * which execute editor commands as configured in passed options.
   *
   * @private
   * @param {module:ui/dropdown/dropdownview~DropdownView} dropdownView
   * @param {Array.<module:ui/dropdown/utils~ListDropdownItemDefinition>} options The list of options for the dropdown.
   * @return {Array.<module:core/command~Command>} Commands the list options are interacting with.
   */
  _fillDropdownWithListOptions(dropdownView, options) {
    const { editor } = this;
    const commands = [];
    const itemDefinitions = new Collection();

    for (const option of options) {
      addListOption(option, editor, commands, itemDefinitions);
    }

    addListToDropdown(
      dropdownView,
      itemDefinitions,
      editor.ui.componentFactory,
    );

    return commands;
  }
}

// Adds an option to a list view.
//
// @param {module:table/tableui~DropdownOption} option A configuration option.
// @param {module:core/editor/editor~Editor} editor
// @param {Array.<module:core/command~Command>} commands The list of commands to update.
// @param {Iterable.<module:ui/dropdown/utils~ListDropdownItemDefinition>} itemDefinitions
// A collection of dropdown items to update with the given option.
function addListOption(option, editor, commands, itemDefinitions) {
  const model = (option.model = new ViewModel(option.model));
  const { commandName, bindIsOn } = option.model;

  if (option.type === 'button' || option.type === 'switchbutton') {
    const command = editor.commands.get(commandName);

    commands.push(command);

    model.set({ commandName });

    model.bind('isEnabled').to(command);

    if (bindIsOn) {
      model.bind('isOn').to(command, 'value');
    }
  }

  model.set({
    withText: true,
  });

  itemDefinitions.add(option);
}
