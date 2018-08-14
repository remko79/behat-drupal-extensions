# This example assumes you have pathauto and redirect installed and can fetch nodes as JSON using the
# Drupal 8 rest endpoint.
# Pathauto should be configured that articles have a url pattern of: "/article/[node:nid]/[node:title]"
Feature: Article JSON output
  I want to test the json output for node type 'article'.

  Background: Generate content
    Given "tags" terms:
      | name |
      | tag1 |
      | tag2 |
      | tag3 |
      | tag4 |

    And "article" content ordered by date:
      | title             | status | field_tags |
      | Example article 1 | 1      | tag1, tag3 |
      | Example article 2 | 1      |            |
      | Example article 3 | 0      |            |

  Scenario: Check JSON output
    When I send a GET request to "/an-invalid-url-whatevah?_format=json"
    Then the response status code should be 404

    # Validate that internal node/<id> urls are forwarded to the url alias
    When I fetch the node with type "article" and title "Example article 1" by the internal node url as JSON
    Then the response status code should be 301
    And the header "Location" should contain "/article"
    And the header "Location" should contain "/example-article-1"

    # Validate that an unpublished article gives a 403
    When I fetch the node with type "article" and title "Example article 3" by the url alias as JSON
    Then the response status code should be 403

    When I fetch the node with type "article" and title "Example article 1" by the url alias as JSON
    Then the response status code should be 200
    And the response should be in JSON
