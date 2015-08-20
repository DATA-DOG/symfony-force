Feature: see api version

  In order to know api version
  As an api user
  I should be able to get version details anonymously

  Scenario: anonymous user can view api version details
    When I send GET request to "/api/version"
    Then the response code should be 200
    And the response should contain json:
      """
      {
        "name": "symfony-force",
        "version": "0.1.0",
        "description": "Pragmatic symfony2 application bootstrap guide."
      }
      """
