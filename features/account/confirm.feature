@user
Feature: Confirming an account
  In order to use application
  As an unconfirmed user
  I need to be able to confirm my account

  Scenario: should be able to confirm an account
    Given I have signed up as "luke.skywalker@datadog.lt"
    When I follow the confirmation link in my email
    Then I should see "Account details" on page headline
    # Then my account "luke.skywalker@datadog.lt" should be confirmed

  Scenario: confirm an account
    Given I have signed up as "luke.skywalker@datadog.lt"
    When I follow the confirmation link in my email
    And I fill in my personal details
    Then I should see success notification "The user Luke Skywalker was successfully confirmed"
