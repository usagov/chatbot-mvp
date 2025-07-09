import { Plugin } from 'ckeditor5/src/core';
import UswdsGridEditing from './editing';
import UswdsGridUi from './ui';
import UswdsGridToolbar from './toolbar';

class UswdsGrid extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [UswdsGridEditing, UswdsGridUi, UswdsGridToolbar];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'UswdsGrid';
  }
}

export default {
  UswdsGrid,
};
