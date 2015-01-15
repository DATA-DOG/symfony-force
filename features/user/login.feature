@user
Feature: Logging in

  In order to use application and manage private resources
  As a registered user
  I need to be able to login

  Background:
    Given confirmed user named "Chewbacca"

  Scenario: can't login with incorrect credentials
    Given I am on page "User Login"
    When I try to login as "Chewbacca" using password "any"
    Then I should see danger notification "Is incorrect your email or password."

