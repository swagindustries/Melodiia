Feature:
  As a user of Symfony and Happii
  I should be able to build easily an API

  Scenario: given I set up manually an empty API
    I specify the following package configuration:
      """
      happii:
          test-api: ~
      """
    And I start the application
    Then I should be able to see the documentation
    And the documentation should be empty

