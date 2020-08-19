Feature:
  As a developer
  I can access easily to the documentation of my API

  Scenario: access the documentation
    Given I make a GET request on "/documentation"
    Then the status code of last response should be 200
