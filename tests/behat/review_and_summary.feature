@quizaccess @quizaccess_deadman @javascript
Feature: Test there is no warning during review or summary
    As a student
    When I review my quiz or when I look at its summary
    The plugin should not inject the warning box into the page

  Background:
    Given the following config values are set as admin:
      | enabledefault | 1 | quizaccess_deadman |
    And the following "users" exist:
      | username     |
      | student      |
      | otherstudent |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user         | course | role    |
      | student      | C1     | student |
      | otherstudent | C1     | student |
    And the following "activity" exists:
      | activity       | quiz   |
      | course         | C1     |
      | idnumber       | 00001  |
      | name           | Quiz 1 |
      | deadman_enable | 1      |
    And the following "question categories" exist:
      | contextlevel | reference | name |
      | Course       | C1        | Cat1 |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext   |
      | Cat1             | truefalse | Q1   | First question |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | Q1       | 1    |
    And user "student" has attempted "Quiz 1" with responses:
      | slot | response |
      | 1    | True     |

  Scenario: Test review
    When I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I follow "Review"
    And I wait until the page is ready
    Then "" "quizaccess_deadman > warning" should not exist

  Scenario: Test summary
    When I am on the "Quiz 1" "mod_quiz > View" page logged in as "otherstudent"
    And I press "Attempt quiz"
    And I click on "False" "radio" in the "First question" "question"
    And I press "Finish attempt ..."
    And I wait until the page is ready
    Then "" "quizaccess_deadman > warning" should not exist
