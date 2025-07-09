/**
 * @file This is what CKEditor refers to as a master (glue) plugin. Its role is
 * just to load the “editing” and “UI” components of this Plugin. Those
 * components could be included in this file, but
 *
 * I.e, this file's purpose is to integrate all the separate parts of the plugin
 * before it's made discoverable via index.js.
 */
import { Plugin } from 'ckeditor5/src/core';
import UswdsOverridesEditingLinks from './uswds_override_link_editing';
import UswdsOverridesEditingLists from './uswds_override_lists_editing';
import UswdsOverridesEditingTables from './uswds_override_tables_editing';

export default class UswdsOverrides extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [
      UswdsOverridesEditingLinks,
      UswdsOverridesEditingLists,
      UswdsOverridesEditingTables,
    ];
  }
}
