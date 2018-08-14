<?php

namespace Behat\remko79\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Context functions related to the menu to be used in behat tests.
 */
class MenuContext extends RawDrupalContext {

  /**
   * Keep track of menu links so they can be cleaned up.
   *
   * @var array
   */
  protected $menuLinks = [];

  /**
   * Create menu links as provided in the table.
   *
   * Example:
   * | title  | link             | description | menu_name | enabled | weight |
   * | Nieuws | internal:/nieuws | News page   | main      | 1       | 0      |
   * | ...    | ...              | ...         | ...       | ...     | ...    |
   *
   * @Given the menu links:
   */
  public function createMenuLinks(TableNode $table) {
    foreach ($table->getHash() as $hash) {
      $menuLink = (object) $hash;
      $saved = $this->getDriver()->createEntity('menu_link_content', $menuLink);
      $this->menuLinks[] = $saved;
    }
  }

  /**
   * Remove any created menu links.
   *
   * @AfterScenario
   */
  public function cleanMenuLinks() {
    // Remove any nodes that were created.
    foreach ($this->menuLinks as $menuLink) {
      $this->getDriver()->entityDelete('menu_link_content', $menuLink);
    }
    $this->menuLinks = [];
  }

}
