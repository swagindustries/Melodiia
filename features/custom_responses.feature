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
          "content": "foo"
        }
      ]
    }
    """
