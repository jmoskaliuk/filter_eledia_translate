@filter @filter_translations
Feature: Manage content translation glossary entries
  In order to keep terminology consistent
  As an administrator
  I need to create and view glossary entries

  Background:
    Given I log in as "admin"

  Scenario: Create a global glossary entry
    When I visit "/filter/translations/manageglossary.php"
    And I follow "Create glossary entry"
    And I set the following fields to these values:
      | Source phrase   | Learning path |
      | Target phrase   | Lernpfad      |
      | sourcelanguage  | en            |
      | targetlanguage  | de            |
      | Glossary scope  | Global / all courses |
      | Status          | Approved      |
      | Priority        | 10            |
    And I press "Save changes"
    Then I should see "Learning path"
    And I should see "Lernpfad"
    And I should see "Approved"

  Scenario: Filter glossary entries by language
    Given I visit "/filter/translations/editglossaryentry.php"
    And I set the following fields to these values:
      | Source phrase   | Course room |
      | Target phrase   | Kursraum    |
      | sourcelanguage  | en          |
      | targetlanguage  | de          |
      | Status          | Reviewed    |
    And I press "Save changes"
    When I set the following fields to these values:
      | Source language | en |
      | Target language | de |
    And I press "Update"
    Then I should see "Course room"
    And I should see "Kursraum"
