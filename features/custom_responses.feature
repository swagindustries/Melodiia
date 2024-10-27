Feature:
  As a developer
  I should be able to render custom response easily with Melodiia

  Scenario: return array of object for a response
    Given there are some todos
    And I make a GET request on "/todos/contains/foo"
    Then the last response contains:
    """
    {
      "meta": {
        "totalPages": 1,
        "totalResults": 1,
        "currentPage": 1,
        "maxPerPage": 1
      },
      "links": {
        "prev": null,
        "next": null,
        "last": "http://localhost/todos/contains/foo",
        "first": "http://localhost/todos/contains/foo"
      },
      "data": [
        {
          "id": 1,
          "content": "foo",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        }
      ]
    }
    """

  Scenario: In debug mode, when an exception occurred I should get a response with stacktrace
    When I make a GET request on "/error"
    Then I should retrieve a stacktrace formatted in JSON

  Scenario: return ok content response
    Given there are some todos
    And I make a PATCH request on "/todos/1/archive"
    Then the last response is empty
    And the http code is 204
