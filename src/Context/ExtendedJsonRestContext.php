<?php

namespace Behat\remko79\Context;

use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Drupal;
use Exception;

/**
 * Extends the default RestContext with extra features for fetching as JSON.
 */
class ExtendedJsonRestContext extends RestContext {

  const JSON_PARAMETER = '?_format=json';

  /**
   * Constructor.
   */
  public function __construct(Request $request) {
    parent::__construct($request);

  }

  /**
   * Sends a HTTP request with the standard _format=json GET parameter.
   *
   * @Given I send a JSON GET request to :url
   */
  public function fetchUrlAsJson($url) {
    return $this->iSendARequestTo('GET', $url . static::JSON_PARAMETER);
  }

  /**
   * Fetch JSON output of a node by the internal '/node/<id>' url.
   *
   * @param string $type
   *   Node type.
   * @param string $title
   *   Node title.
   *
   * @return \Behat\Mink\Element\DocumentElement
   *   The result of the request.
   *
   * @throws \Exception
   *   On failure.
   *
   * @Given I fetch the node with type :type and title :title by the internal node url as JSON
   */
  public function fetchNodeByInternalIdAsJson($type, $title) {
    $node = $this->getEntityByTitle('node', $type, $title);
    return $this->fetchUrlAsJson('/node/' . $node->id());

  }

  /**
   * Fetch JSON output of a node by the url alias.
   *
   * @param string $type
   *   Node type.
   * @param string $title
   *   Node title.
   *
   * @return \Behat\Mink\Element\DocumentElement
   *   The result of the request.
   *
   * @throws \Exception
   *   On failure.
   *
   * @Given I fetch the node with type :type and title :title by the url alias as JSON
   */
  public function fetchNodeByUrlAliasAsJson($type, $title) {
    $node = $this->getEntityByTitle('node', $type, $title);
    $url = $node->toUrl()->toString();
    return $this->fetchUrlAsJson($url);
  }

  /**
   * Find entity by title.
   *
   * @param string $entityType
   *   Entity type, for example 'node' or 'user'.
   * @param string $bundle
   *   Entity bundle, for example 'article'.
   * @param string $title
   *   Title of the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The loaded entity.
   *
   * @throws \Exception
   */
  public function getEntityByTitle($entityType, $bundle, $title) {
    $entityTypeManager = Drupal::entityTypeManager();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $entityTypeManager->getStorage($entityType);
    $keys = $entityTypeManager->getDefinition($entityType)->getKeys();
    $query = $storage->getQuery()->condition($keys['label'], $title);
    if ($bundle) {
      $query->condition($keys['bundle'], $bundle);
    }
    $ids = $query->execute();
    if (!empty($ids)) {
      return $storage->load(end($ids));
    }
    throw new Exception(sprintf("Entity '%s' (%s) with title: '%s' not found", $bundle, $entityType, $title));
  }

}
