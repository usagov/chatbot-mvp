/**
 * @file registers the grid toolbar button and binds functionality to it.
 */
import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import icon from '../../../../images/icons/grid/grid.svg';

export default class UswdsGridUI extends Plugin {
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

    // This will register the grid toolbar button.
    editor.ui.componentFactory.add('uswdsGrid', (locale) => {
      const command = editor.commands.get('insertUswdsGrid');
      const buttonView = new ButtonView(locale);

      // Create the toolbar button.
      buttonView.set({
        label: editor.t('Uswds Grid'),
        icon,
        tooltip: true,
      });

      // Bind the state of the button to the command.
      buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
      this.listenTo(buttonView, 'execute', () => {
        openDialog(
          dialogURL,
          ({ settings }) => {
            editor.execute('insertUswdsGrid', settings);
          },
          dialogSettings,
        );
      });

      return buttonView;
    });
  }
}
