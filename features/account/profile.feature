@user
Feature: Profile management
  In order to keep account up to date
  As a confirmed user
  I need to be able to update my profile

  Background:
    Given confirmed user named "General Grievous"
    And I'm logged in as "general.grievous@datadog.lt"

  Scenario: can update without filling in password
    Given I am on "profile" page
    When I fill in "Firstname" with "Dark"
    And I press "Update"
    Then I should see success notification "Updated your profile may be."

  Scenario: can change password
    Given I am on "profile" page
    When I fill in "Password" with "S3cretpass"
    And I fill in "Repeat password" with "S3cretpass"
    And I press "Update"
    Then I should see success notification "Updated your profile may be."
