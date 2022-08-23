Feature:
  In order to create, update and delete data using the API
  If my data is wrong for the API, a formatted error is returned

  Scenario: Create a todo without content is not possible and should return validation errors
    Given I create a todo without required information
    Then the status code is 400
    Then a violation for "content" should exist
    Then a violation for "publishDate" should exist
