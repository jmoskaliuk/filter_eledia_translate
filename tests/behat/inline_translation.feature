@filter @filter_translations @javascript
Feature: Use the inline translation menu
  In order to edit translations in context
  As an administrator
  I need the translation menu in the navbar to open and toggle inline translation mode

  Background:
    Given the "translations" filter is "on"
    And the "translations" filter applies to "content and headings"
    And I log in as "admin"

  Scenario: Open the translation menu from the navbar
    When I am on site homepage
    And I click on ".translation-icon-wrapper [data-bs-toggle='dropdown']" "css_element"
    Then I should see "Start in-line translation"
    And I should see "Manage translations"
    And I should see "Manage glossary"

  Scenario: Start inline translation mode from the navbar menu
    When I am on site homepage
    And I click on ".translation-icon-wrapper [data-bs-toggle='dropdown']" "css_element"
    And I click on "Start in-line translation" "link"
    And I click on ".translation-icon-wrapper [data-bs-toggle='dropdown']" "css_element"
    Then I should see "Stop in-line translation"
