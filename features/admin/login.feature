@user
Feature: Logging in to administrator panel
  In order to use manage site
  As a administrator
  I need to be able to login

  Scenario: can't login with unprivileged user
    Given confirmed user named "Chewbacca Wookiee"
    And I'm logged in as "chewbacca.wookiee@datadog.lt"
    When I visit "admin" page
    Then the response code should be 403

  Scenario: admin user is able to login
    Given confirmed admin named "Chewbacca Wookiee"
    And I'm logged in as "chewbacca.wookiee@datadog.lt"
    When I visit "admin" page
    Then I should see "Deathstar:Admin"

