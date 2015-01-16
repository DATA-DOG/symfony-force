@user
Feature: Signing up

  In order to use application
  As an anonymous user
  I need to be able to signup

  Scenario: signup with valid email
    Given I am on page "User Signup"
    When I signup as "luke.skywalker@datadog.lt"
    Then I should see success notification "The confirmation email should soon be received"
    And I should be on page "User Login"

  Scenario: cannot signup with a confirmed email address
    Given confirmed user named "Luke Skywalker"
    And I am on page "User Signup"
    When I attempt to signup as "luke.skywalker@datadog.lt"
    Then I should see a form field error "Confirmed already is the email luke.skywalker@datadog.lt."

  Scenario: signing up with unconfirmed email resends confirmation token
    Given unconfirmed user named "Luke Skywalker"
    And I am on page "User Signup"
    When I attempt to signup as "luke.skywalker@datadog.lt"
    Then I should see info notification "To the luke.skywalker@datadog.lt address the confirmation email was resent"

