<?php

namespace Drupal\uswds_ckeditor_integration\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Filter to apply USWDS Stacked attributes.
 */
#[Filter(
  id: "filter_table_attributes",
  title: new TranslatableMarkup("USWDS Stacked Table Attributes CK5"),
  type: FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
  description: new TranslatableMarkup("Apply USWD table stacked attributes. With CKEditor5 in mind."),
)]
class FilterUswdsTableStacked extends FilterBase {

  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    $result = new FilterProcessResult($text);

    if (stristr($text, 'table') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      $tables = $xpath->query('//table[contains(@class, "usa-table--stacked")]');

      // Add USWDS Class to table.
      foreach ($tables as $table) {
        $rows = $xpath->query('.//tr', $table);
        $headers = $xpath->query('.//thead//th', $table);

        if ($headers->length > 0) {
          $table_headers = [];
          // Add attributes to column headers.
          foreach ($headers as $header) {
            $header->setAttribute('scope', 'col');
            $label = $header->nodeValue;
            $table_headers[] = $label;
          }

          // Add scope to row.
          $skip_first = TRUE;
          foreach ($rows as $row) {
            if ($skip_first) {
              $skip_first = FALSE;
              continue;
            }
            $counter = 0;
            foreach ($row->childNodes as $key => $tag) {
              switch ($tag->nodeName) {
                case 'td':
                  $data_label = $table_headers[$counter];
                  $tag->setAttribute('data-label', $data_label);
                  if ($key === 0) {
                    $tag->setAttribute('scope', 'row');
                  }
                  $counter++;
                  break;

                default:
                  break;
              }
            }
          }
          $result->setProcessedText(Html::serialize($dom));
        }
        else {
          $this->getLogger('uswds_ckeditor_integration')->warning('Table without header trying to use USWDS stacked, not setting header is an accessibility issue.');
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Filter to convert usa-table-stacked into responsive markup.');
  }

}
