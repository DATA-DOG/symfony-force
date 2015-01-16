@user
Feature: Logging in

  In order to use application and manage private resources
  As a registered user
  I need to be able to login

  Background:
    Given confirmed user named "Chewbacca"

  Scenario: can't login with incorrect credentials
    Given I am on page "User Login"
    When I try to login as "chewbacca@datadog.lt" using password "any"
    Then I should see danger notification "Is incorrect your email or password."

  Scenario: confirmed user is able to login
    Given I am on page "User Login"
    When I try to login as "chewbacca@datadog.lt" using password "S3cretpassword"
    Then I should be on page "Homepage"
    And I should be logged in

  Scenario: unconfirmed user cannot login
    Given unconfirmed user named "Darth Vader"
    And I am on page "User Login"
    When I try to login as "darth.vader@datadog.lt" using password "S3cretpassword"
    Then I should see danger notification "Is incorrect your email or password."
