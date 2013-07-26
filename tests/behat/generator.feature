@tool_advancedspamcleaner
Feature: Create spam data

  Scenario:
    Given the following "users" exists:
      | username | firstname | lastname | email |
      | testuser | Test | User | moodle@moodlemoodle.com |
    And I log in as "testuser"
    And I am on homepage
    And I expand "My profile" node
    And I expand "Blogs" node
    And I follow "Add a new entry"
    And I fill the moodle form with:
      | Entry title | Blog post from user 1 |
      | Blog entry body | User 1 blog post content |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Commenting on my own blog entry
    Given I am on homepage
    And I log in as "testuser"
    And I am on homepage
    And I expand "My profile" node
    And I expand "Blogs" node
    And I follow "View all of my entries"
    And I follow "Blog post from user 1"
    And I should see "User 1 blog post content"
    And I follow "Comments (0)"
    When I fill in "content" with "$My own >nasty< \"string\"!"
    And I follow "Save comment"
    And I wait "4" seconds
    Then I should see "$My own >nasty< \"string\"!"
    And I fill in "content" with "Another $Nasty <string?>"
    And I follow "Save comment"
    And I wait "4" seconds
    And I should see "Comments (2)" in the ".comment-link" "css_element"

  Scenario: Creating a forum with discussion
    Given the following "users" exists:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exists:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exists:
      | user | course | role |
      | student1 | C1 | student |
    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Security" node
    And I follow "Site policies"
    And I select "1 minutes" from "Maximum time to edit posts"
    And I press "Save changes"
    And I am on homepage
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Forum" to section "1" and I fill the form with:
      | Forum name | Test forum name |
      | Forum type | Standard forum for general use |
      | Description | Test forum description |
    And I log out
    And I follow "Course 1"
    And I log in as "student1"
    And I add a new discussion to "Test forum name" forum with:
      | Subject | Forum post subject |
      | Message | This is the body |

  Scenario: Sending a message
    Given the following "users" exists:
      | username | firstname | lastname | email |
      | user1 | User | One | one@asd.com |
      | user2 | User | Two | two@asd.com |
    And I log in as "user1"
    And I send "Message 1 from user1 to user2" message to "user2"
    And I send "Message 2 from user1 to user2" message to "user2"