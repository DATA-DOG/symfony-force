@user
Feature: Confirming an account

  In order to use application
  As an unconfirmed user
  I need to be able to confirm my account

  Scenario: should see account confirmation page
    Given unconfirmed user named "Luke Skywalker"
    When I follow confirmation link from my "luke.skywalker@datadog.lt" email
    Then I should be on page "Account Confirmation" with params:
      | token | luke-skywalker-token |

  Scenario: confirm an account
    When I confirm my account "Luke Skywalker" with personal details
    Then I should see success notification "The user Luke Skywalker confirmed may be."
