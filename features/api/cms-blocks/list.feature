Feature: get cms blocks

  In order to see cms blocks
  As an api user
  I should be able to get list of cms blocks

  Scenario: can list cms blocks
    When I send GET request to "/api/cms-blocks"
    Then the response code should be 200
    And the response should contain json:
      """
      {
        "blocks": [
          {
            "id": 1,
            "name": "Footer"
          },
          {
            "id": 2,
            "name": "Css"
          },
          {
            "id": 3,
            "name": "Js"
          }
        ]
      }
      """

  Scenario: can get paged cms blocks
    When I send GET request to "/api/cms-blocks?page=2&limit=1"
    Then the response code should be 200
    And the response should match json:
      """
      {
        "blocks": [
          {
            "id": 2,
            "alias": "css",
            "name": "Css",
            "content": "\/* cms block for css *\/",
            "createdAt": "/(.+)/",
            "updatedAt": "/(.+)/"
          }
        ]
      }
      """
