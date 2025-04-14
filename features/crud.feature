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
        "last": "http://localhost/todos?page=1",
        "first": "http://localhost/todos?page=1"
      },
      "data": [
        {
          "id": 1,
          "content": "foo",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 2,
          "content": "bar",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 3,
          "content": "baz",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        }
      ]
    }
    """

  Scenario: create a todo and get it
    Given I make a "POST" request on "/todos" with the content:
    """
    {
      "content": "hello",
      "publishDate":"2050-01-02T00:00:00+00:00"
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
      "content": "hello",
      "publishDate":"2050-01-02T00:00:00+00:00",
      "archived": false
    }
    """

  Scenario: filter todo
    Given there are some todos
    When I make a GET request on "/todos?q=ba"
    Then the last response contains:
    """
    {
      "meta": {
        "totalPages": 1,
        "totalResults": 2,
        "currentPage": 1,
        "maxPerPage": 30
      },
      "links": {
        "prev": null,
        "next": null,
        "last": "http://localhost/todos?q=ba&page=1",
        "first": "http://localhost/todos?q=ba&page=1"
      },
      "data": [
        {
          "id": 2,
          "content": "bar",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 3,
          "content": "baz",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        }
      ]
    }
    """

  Scenario: I can delete todo
    Given there is one todo "hello Melodiia"
    When I make a DELETE request on "/todos/1" with the content:
    """
    []
    """
    Then todo with content "hello Melodiia" should not exists

  Scenario: many pages
    Given there are many todos
    When I make a GET request on "/todos?q=ba&max_per_page=3"
    Then the last response contains:
    """
    {
      "meta": {
        "totalPages": 3,
        "totalResults": 7,
        "currentPage": 1,
        "maxPerPage": 3
      },
      "links": {
        "prev": null,
        "next": "http://localhost/todos?max_per_page=3&q=ba&page=2",
        "last": "http://localhost/todos?max_per_page=3&q=ba&page=3",
        "first": "http://localhost/todos?max_per_page=3&q=ba&page=1"
      },
      "data": [
        {
          "id": 2,
          "content": "bar",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 3,
          "content": "baz",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 4,
          "content": "bak",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        }
      ]
    }
    """
    When I make a GET request on "/todos?q=ba&max_per_page=3&page=2"
    Then the last response contains:
    """
    {
      "meta": {
        "totalPages": 3,
        "totalResults": 7,
        "currentPage": 2,
        "maxPerPage": 3
      },
      "links": {
        "prev": "http://localhost/todos?max_per_page=3&page=1&q=ba",
        "next": "http://localhost/todos?max_per_page=3&page=3&q=ba",
        "last": "http://localhost/todos?max_per_page=3&page=3&q=ba",
        "first": "http://localhost/todos?max_per_page=3&page=1&q=ba"
      },
      "data": [
        {
          "id": 5,
          "content": "baf",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 6,
          "content": "bas",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        },
        {
          "id": 7,
          "content": "bab",
          "publishDate":"2050-01-02T00:00:00+00:00",
          "archived": false
        }
      ]
    }
    """
