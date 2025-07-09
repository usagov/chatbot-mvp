import { Plugin } from 'ckeditor5/src/core';
import ParagraphsEmbedEditing from './paragraphsembedediting';
import ParagraphsEmbedUI from './paragraphsembedui';

export default class ParagraphsEmbed extends Plugin {

  static get requires() {
    return [ParagraphsEmbedEditing, ParagraphsEmbedUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'ParagraphsEmbed';
  }

}
