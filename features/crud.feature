Feature:
  In order to create, read, update and delete data using the API
  As a client software
  I should be able use standard REST CRUD actions with todos

  Scenario: list some todo
    Given there are some todos
    When I make a GET request on "/todos"
    Then I should retrieve:
    """
    {
      "meta": {
        "totalPages": 1,
        "totalResults": 3,
        "currentPage": 1,
        "maxPerPage": 30
      },
      "links": {
        "prev": null,
        "next": null,
        "last": "http://localhost/todos",
        "first": "http://localhost/todos"
      },
      "data": [
        {
          "id": 1,
          "content": "foo"
        },
        {
          "id": 2,
          "content": "bar"
        },
        {
          "id": 3,
          "content": "baz"
        }
      ]
    }
    """

  Scenario: create a todo and get it
    Given I make a "POST" request on "/todos" with the content:
    """
    {
      "content": "hello"
    }
    """
    And the last response contains:
    """
    {
      "id": 1
    }
    """
    When I make a GET request on "/todos/1"
    Then I should retrieve:
    """
    {
      "id": 1,
      "content": "hello"
    }
    """
