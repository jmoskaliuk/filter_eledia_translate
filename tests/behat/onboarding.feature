@filter @filter_translations
Feature: Configure content translations from the onboarding workflow
  In order to set up content translations without hunting through hidden filter settings
  As an administrator
  I need a guided onboarding page

  Background:
    Given I log in as "admin"

  Scenario: The onboarding workflow exposes all setup steps
    When I visit "/filter/translations/onboarding.php"
    Then I should see "Content translations onboarding"
    And I should see "Filter"
    And I should see "Course control"
    And I should see "DeepL and providers"
    And I should see "Logging"
    And I should see "Glossary"
    And I should see "Finish"

  Scenario: Enable the filter and headings from onboarding
    When I visit "/filter/translations/onboarding.php"
    And I set the field "Enable the Content translations filter globally" to "1"
    And I set the field "Apply the filter to content and headings" to "1"
    And I press "Save and continue"
    Then I should see "Changes saved"
    And I should see "Course control"

  Scenario: Configure DeepL settings without requiring a live API call
    When I visit "/filter/translations/onboarding.php?step=provider"
    And I set the following fields to these values:
      | Use DeepL Translate API       | 1                                        |
      | Back off from erroring API    | 1                                        |
      | API Endpoint                  | https://api-free.deepl.com/v2/translate |
      | API key                       | test-behat-key                          |
      | Source language               | EN                                       |
      | Use DeepL HTML tag handling   | 1                                        |
    And I press "Save and continue"
    Then I should see "Changes saved"
    And I should see "Logging"
