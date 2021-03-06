@user
Feature: Logging in
  In order to use application and manage private resources
  As a registered user
  I need to be able to login

  Background:
    Given confirmed user named "Chewbacca Wookiee"

  Scenario: can't login with incorrect credentials
    Given I am on "login" page
    When I try to login as "chewbacca.wookiee@datadog.lt" using password "any"
    Then I should see error notification "Email or password is incorrect"

  Scenario: confirmed user is able to login
    Given I am on "login" page
    When I try to login as "chewbacca.wookiee@datadog.lt" using password "S3cretpassword"
    Then I should see "Symfony Force Edition" on page headline
    And I should be logged in

  Scenario: unconfirmed user cannot login
    Given unconfirmed user named "Darth Vader"
    And I am on "login" page
    When I try to login as "darth.vader@datadog.lt" using password "S3cretpassword"
    Then I should see error notification "Email or password is incorrect"
