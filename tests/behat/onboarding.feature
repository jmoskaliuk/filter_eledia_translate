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

  Scenario: Configure course control and verify the language selector field setup
    When I visit "/filter/translations/onboarding.php?step=course"
    Then I should see "Course custom field for target languages"
    And I should see "Create course translation fields"
    When I set the following fields to these values:
      | Course control source                         | Course custom fields, then legacy tags |
      | Legacy course tag for enabling translation    | deepl                                  |
      | Course custom field for enabling translation  | eledia_translate_enabled               |
      | Course custom field for target languages      | eledia_translate_languages             |
    And I press "Save and continue"
    Then I should see "Changes saved"
    And I should see "DeepL and providers"
    When I visit "/filter/translations/onboarding.php?step=course"
    And I click on "Create course translation fields" "link"
    Then I should see "Course custom field \"eledia_translate_enabled\" already exists."
    And I should see "Course custom field \"eledia_translate_languages\" already exists."

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
