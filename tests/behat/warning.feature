@quizaccess @quizaccess_deadman @javascript
Feature: Test display of the warning box
    As a student
    In order to know that my browser still processes Javascript in the background
    I must be sure that the warning box will appear if needed

  Background:
    Given the following config values are set as admin:
      | delay          | 3   | quizaccess_deadman |
      | defaultmessage | xxx | quizaccess_deadman |
    And the following "users" exist:
      | username |
      | student  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | student | C1     | student |
    And the following "activity" exists:
      | activity            | quiz              |
      | course              | C1                |
      | idnumber            | 00001             |
      | name                | Quiz 1            |
      | deadman_enable      | 1                 |
      | deadman_warningtext | DEADMAN TRIGGERED |
    And the following "question categories" exist:
      | contextlevel | reference | name |
      | Course       | C1        | Cat1 |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext   |
      | Cat1             | truefalse | Q1   | First question |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | Q1       | 1    |
    And I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  Scenario: Test with running JS
    When I press "Attempt quiz"
    And I wait until the page is ready
    Then "" "quizaccess_deadman > warning" should exist
    And I should not see "DEADMAN TRIGGERED"
    And "" "quizaccess_deadman > warning" should not be visible
    When I wait "10" seconds
    Then I should not see "DEADMAN TRIGGERED"
    And "" "quizaccess_deadman > warning" should not be visible

  Scenario: Test failing JS
    When I press "Attempt quiz"
    And I wait until the page is ready
    And I stop Javascript timers for the deadman quizaccess plugin
    And I wait "5" seconds
    Then I should see "DEADMAN TRIGGERED"
