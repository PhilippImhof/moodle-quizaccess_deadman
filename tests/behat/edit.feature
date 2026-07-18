@quizaccess @quizaccess_deadman @javascript
Feature: Test configuring the dead man's switch for a quiz
    As a teacher
    In order to use the quizaccess_deadman plugin
    I must be able to enable, disable and configure the Dead man's switch for Javascript

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "activity" exists:
      | activity            | quiz      |
      | course              | C1        |
      | idnumber            | quizwith  |
      | name                | Quiz WITH |
      | deadman_enable      | 1         |
      | deadman_warningtext | Foo Bar   |
    And the following "activity" exists:
      | activity       | quiz         |
      | course         | C1           |
      | idnumber       | quizwithout  |
      | name           | Quiz WITHOUT |
      | deadman_enable | 0            |
    And I log in as "teacher"

  Scenario: Message field is hidden/shown according to the plugin's state
    When I am on the "Quiz WITHOUT" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_enable" matches value "0"
    And I should not see "Message" in the "#id_deadmancontainer" "css_element"
    When I set the field "id_deadman_enable" to "1"
    Then I should see "Message" in the "#id_deadmancontainer" "css_element"

  Scenario: Setting fetched from the DB and set in form when editing a quiz
    When I am on the "Quiz WITH" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_enable" matches value "1"
    And the field "id_deadman_warningtext" matches value "Foo Bar"

  Scenario: Settings are saved to the DB when preparing or editing a quiz
    When I am on the "Quiz WITH" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    And I set the field "id_deadman_warningtext" to "NEW FOO BAR"
    And I press "Save and display"
    And I am on the "Quiz WITH" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_warningtext" matches value "NEW FOO BAR"
    When I am on the "Quiz WITH" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    And I set the field "id_deadman_enable" to "No"
    And I press "Save and display"
    And I am on the "Quiz WITH" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_enable" matches value "0"

  Scenario: Configured default value (ON) is used when creating a new quiz
    Given the following config values are set as admin:
      | enabledefault | 1 | quizaccess_deadman |
    When I add a quiz activity to course "Course 1" section "0" and I fill the form with:
      | Name | New Quiz |
    And I am on the "New Quiz" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_enable" matches value "1"

  Scenario: Configured default value (OFF) is used when creating a new quiz
    Given the following config values are set as admin:
      | enabledefault | 0 | quizaccess_deadman |
    When I add a quiz activity to course "Course 1" section "0" and I fill the form with:
      | Name | Other New Quiz |
    And I am on the "Other New Quiz" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_enable" matches value "0"

  Scenario: Configured default message is used when creating a new quiz
    Given the following config values are set as admin:
      | enabledefault  | 1                    | quizaccess_deadman |
      | defaultmessage | This is the message. | quizaccess_deadman |
    When I add a quiz activity to course "Course 1" section "0" and I fill the form with:
      | Name | Other New Quiz |
    And I am on the "Other New Quiz" "quiz activity editing" page
    And I follow "Dead man's switch for Javascript"
    Then the field "id_deadman_warningtext" matches value "This is the message."
