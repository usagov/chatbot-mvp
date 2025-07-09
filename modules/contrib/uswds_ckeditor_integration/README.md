## CONTENTS OF THIS FILE

 * Introduction
 * Shout-outs
 * Features
 * Installation
 * Configuration
 * Maintainers

## INTRODUCTION

With the USWDS library (https://designsystem.digital.gov/) becoming
a requirement for government websites thought it would be useful to
have some integration with the ckeditor. The primary goal is to make
it easy for a user to utilize and inject USWDS classes and
components directly into the ckeditor without opening up the source.

For a full description of this module,
visit the project page: See https://www.drupal.org/project/uswds_ckeditor_integration

To submit bug reports and feature suggestions, or track changes:
See https://www.drupal.org/project/issues/uswds_ckeditor_integration

## Shout-outs!
* Maintainers of [Ckeditor BS Grid](https://www.drupal.org/project/ckeditor_bs_grid)
  * Forked this bootstrap module for USWDS.
* Maintainers of [Embedded Content](https://www.drupal.org/project/embedded_content)
  * Added a few components and transitioned a few to this module.
* Maintainers of [Ckeditor Accordion](https://www.drupal.org/project/ckeditor_accordion)
  * Forked this for USWDS.

## FEATURES

### CKEditor5 Integration
1. Introducing [Embedded Content](https://www.drupal.org/project/embedded_content)
   * [Accordion](https://designsystem.digital.gov/components/accordion/)
   * [Alerts]( https://designsystem.digital.gov/components/alert/)
   * [Process List](https://designsystem.digital.gov/components/process-list/)
   * [Summary Box](https://designsystem.digital.gov/components/summary-box/)
2. USWDS Overrides
   * Default class for links, lists, and tables
3. USWDS Table Attributes See https://designsystem.digital.gov/components/table/
   * Add ability to make tables borderless, scrollable,
     stackable/responsive, and sortable with USWDS classes.

## INSTALLATION

Install as you would normally install a contributed Drupal module.
Visit: https://www.drupal.org/docs/extending-drupal/installing-drupal-modules
for further information.

## CONFIGURATION

Each component requires specific configuration

#### CKEditor5 Integration
Filtering is automatic with ckeditor5.
1. Embedded Content
   * Move button into ckeditor
   * Multiple buttons can be configured under Embedded Content config.
2. Accordion button
   * Move button into ckeditor
3. USWDS Overrides
   * Configuration option in editor. Check each option you wish to apply.
4. USWDS Table Attributes
   * Configuration option in editor. Check if you want to apply.
   * Enable USWDS Stacked Table Attributes CK5 filter.
   * May need to add "div" to source editing section.

## MAINTAINERS

Current maintainers:
* Stephen Mustgrave (smustgrave) (https://www.drupal.org/u/smustgrave)
