# USWDS Paragraph Components

## CONTENTS OF THIS FILE

* Introduction
* Requirements
* Installation
* Configuration
* Maintainers

## INTRODUCTION

This suite of [Paragraphs](https://www.drupal.org/project/paragraphs) bundles
works within the [USWDS](https://designsystem.digital.gov/) framework.

* For a full description of the module, visit the project page:
  https://drupal.org/project/uswds_paragraph_components
  or
  https://www.drupal.org/docs/8/modules/uswds-paragraph-components

* To submit bug reports and feature suggestions, or to track changes:
  https://drupal.org/project/issues/uswds_paragraph_components

**Bundle Types:**

Starting 2.4.x I moved the bundles into submodules so site builders can pick bundles.

* [USWDS Accordion](https://designsystem.digital.gov/components/accordion/)
  * Submodule: USWDS Paragraph Components Accordions
* [USWDS Alert](https://designsystem.digital.gov/components/alert/)
  * Submodule: USWDS Paragraph Components Alerts
* Cards
  * [USWDS Card Group (Flag)](https://designsystem.digital.gov/components/card/)
  * [USWDS Card Group (Regular)](https://designsystem.digital.gov/components/card/)
  * Submodule: USWDS Paragraph Components Cards, USWDS Paragraph Components Breakpoints
* [USWDS Layout Grid](https://designsystem.digital.gov/utilities/layout-grid/)
  * Submodule: USWDS Paragraph Components Columns, USWDS Paragraph Components Breakpoints
* [USWDS Modal](https://designsystem.digital.gov/components/modal/)
  * Submodule: USWDS Paragraph Components Modal
* [USWDS Process List](https://designsystem.digital.gov/components/process-list/)
  * Submodule: USWDS Paragraph Components Process List
* [USWDS Step Indicator](https://designsystem.digital.gov/components/step-indicator/)
  * Submodule: USWDS Paragraph Components Step Indicator
* [USWDS Summary Box](https://designsystem.digital.gov/components/summary-box/)
  * Submodule: USWDS Paragraph Components Summary Box

## REQUIREMENTS

This module requires the following modules outside of Drupal core:

* [Entity Reference Revisions](https://www.drupal.org/project/entity_reference_revisions)
* [Paragraphs](https://www.drupal.org/project/paragraphs)
* [Views Reference Field](https://www.drupal.org/project/viewsreference)
* [Field Group](https://www.drupal.org/project/field_group)
* USWDS framework's CSS and JS included in your theme. https://designsystem.digital.gov/

## Recommended Modules/Themes

* [USWDS - United States Web Design System Base](https://www.drupal.org/project/uswds_base)
* [USWDS Bootstrap Layout Builder Configuration](https://www.drupal.org/project/uswds_blb_configuration)

## INSTALLATION

* Install as you would normally install a contributed Drupal module. Visit:
  https://www.drupal.org/node/1897420 for further information.
* Verify installation by visiting /admin/structure/paragraphs_type and seeing
  your new Paragraph bundles.


## CONFIGURATION

* Create or edit the relevant content type in your Drupal project.
* Add a new field of type Paragraphs under the "Reference Revisions" group.
* Allow unlimited so creators can add more than one Paragraph to each node.
* On the field edit screen, you can add instructions, and choose which
  bundles you want to allow for this field.
* Don't select the following as they are sub bundles used by a parent bundle. By themselves they won't do anything.
  * USWDS Cards (Flag)
  * USWDS Cards (Regular)
  * USWDS Accordion
  * USWDS Process Item
  * USWDS Step Indicator Item


* The entity forms for these bundles are pretty complex and not visually appealing. Based on your needs or
  client needs, feel free to hide certain fields.

## RESET BUNDLES
NOTE THIS FUNCTIONALITY IS CURRENTLY BROKEN.
* Go to /admin/config/content/uswds_paragraph_components
* Select the bundles you wish to reset
* Click save.  This will reimport the module config into your database.

## MAINTAINERS

Current maintainers:
* [smustgrave](https://www.drupal.org/u/smustgrave)

