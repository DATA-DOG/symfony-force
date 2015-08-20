Feature: protected API resources

  In order to protect resources
  As an api user
  I should not be able to access certain API resources without authentication

  Scenario: request without an authentication token should result in error
    When I send GET request to "/api/cms-blocks"
    Then the response code should be 401

  Scenario: authentication should allow only POST method
    When I send GET request to "/api/authenticate"
    Then the response code should be 405

  Scenario: authentication requires credentials
    When I send POST request to "/api/authenticate" with:
      """
      {
        "please": "log me in"
      }
      """
    Then the response code should be 401
    And there should be an error message "Username or password is not valid." in response

  Scenario: try to authenticate when there is no such user
    When I try to authenticate as "unavailable@datadog.lt"
    Then the response code should be 401

  Scenario: should be able to authenticate a confirmed user
    Given confirmed user named "John Doe"
    When I try to authenticate as "john.doe@datadog.lt"
    Then the response code should be 200
    And the response should contain json:
      """
      {
        "id": %john.doe@datadog.lt%,
        "roles": ["ROLE_USER"],
        "token": "/(.+)/"
      }
      """

  Scenario: should not be able to authenticate an unconfirmed user
    Given unconfirmed user named "John Doe"
    When I try to authenticate as "john.doe@datadog.lt"
    Then the response code should be 401
