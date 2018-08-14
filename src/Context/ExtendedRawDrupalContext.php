<?php

namespace Behat\remko79\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\node\NodeInterface;

/**
 * Adds several common context functions.
 */
class ExtendedRawDrupalContext extends RawDrupalContext {

  /**
   * Creates content of a given type.
   *
   * If created, changed or status values aren't set, they are given a default.
   * The first nodes created will be the 'oldest' nodes.
   *
   * @param string $type
   *   Content type, i.e. 'artikel'.
   * @param \Behat\Gherkin\Node\TableNode $nodesTable
   *   List of nodes to create.
   *
   * @Given :type content ordered by date:
   *
   * @see \Drupal\DrupalExtension\Context\DrupalContext::cleanNodes
   */
  public function createNodesOrderedByDate($type, TableNode $nodesTable) {
    $hash = $nodesTable->getHash();

    $cnt = count($hash);
    $time = time();
    foreach ($hash as $nodeHash) {
      $node = (object) $nodeHash;
      $node->type = $type;
      if (!isset($node->created)) {
        $node->created = $time - $cnt;
      }
      if (!isset($node->changed)) {
        $node->changed = $node->created;
      }
      if (!isset($node->status)) {
        $node->status = NodeInterface::PUBLISHED;
      }
      if (empty($node->field_sectie)) {
        unset($node->field_sectie);
      }
      if (empty($node->field_tags)) {
        // Remove tags if empty, else behat tries to link the node to
        // tags with empty name.
        unset($node->field_tags);
      }

      $this->nodeCreate($node);
      $cnt--;
    }
  }

  /**
   * Delete files which aren't in the file_usage table anymore.
   *
   * Note: this has been disabled in Drupal 8.4.0
   * (see https://www.drupal.org/node/2801777 for details).
   *
   * @AfterFeature
   */
  public static function cleanUpUnusedFiles() {
    $db = \Drupal::database();

    $fUsageQuery = $db->select('file_usage', 'fu')
      ->fields('fu', ['fid']);

    $fidQuery = $db->select('file_managed', 'fm')
      ->fields('fm', ['fid']);
    $fidQuery->condition('fm.fid', $fUsageQuery, 'NOT IN');

    $fids = $fidQuery->execute()->fetchCol();
    if (!empty($fids)) {
      file_delete_multiple($fids);
    }
  }

}
