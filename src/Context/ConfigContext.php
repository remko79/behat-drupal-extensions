<?php

namespace Behat\remko79\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Serialization\Yaml;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Context for importing configuration files.
 */
class ConfigContext extends RawDrupalContext {

  /**
   * Key of configuration keys made during a scenario.
   *
   * @var array
   */
  protected $testConfigs = [];

  /**
   * Import a config (yml) file from behat/test-configs directory.
   *
   * @param \Behat\Gherkin\Node\TableNode $files
   *   Configuration files to import (without the .yml suffix).
   *
   * @Given I want to import the following config files:
   */
  public function importConfigFromTestConfigsDirectory(TableNode $files) {
    foreach ($files->getHash() as $fileName) {
      $configName = $fileName['filename'];
      $filename = __DIR__ . '/../../test-configs/' . $configName . '.yml';
      if (!is_file($filename)) {
        throw new \Exception('Invalid filename: ' . $configName);
      }
      $fileContents = file_get_contents($filename);
      if ($fileContents === FALSE) {
        throw new \Exception('Unable to read contents from file: ' . $configName);
      }

      $data = Yaml::decode($fileContents);
      // A simple string is valid YAML for any reason.
      if (!is_array($data)) {
        throw new \Exception('Invalid file, should be a YAML configuration file');
      }

      $configImporter = $this->getConfigImporter(TRUE, $configName, $data);
      if ($configImporter === FALSE) {
        // No change.
        return;
      }
      $configImporter->import();

      $this->testConfigs[] = $configName;
    }
    // Rebuild the router if needed. This isn't done by default due to importing directly.
    \Drupal::service('router.builder')->rebuildIfNeeded();
  }

  /**
   * Gets the configuration importer.
   *
   * @param bool $shouldImport
   *   TRUE to import, FALSE to delete.
   * @param string $configName
   *   Configuration name.
   * @param array $data
   *   The configuration data (only required when needs to be imported).
   *
   * @return \Drupal\Core\Config\ConfigImporter|false
   *   The configuration importer or FALSE when nothing has to be imported.
   */
  protected function getConfigImporter($shouldImport, $configName, array $data = []) {
    $container = \Drupal::getContainer();

    /** @var \Drupal\Core\Config\StorageInterface $configStorage */
    $configStorage = $container->get('config.storage');
    $sourceStorage = new StorageReplaceDataWrapper($configStorage);
    if ($shouldImport) {
      $sourceStorage->replaceData($configName, $data);
    }
    else {
      $sourceStorage->delete($configName);
      return TRUE;
    }
    /** @var \Drupal\Core\Config\ConfigManagerInterface $configManager */
    $configManager = $container->get('config.manager');
    $storageComparer = new StorageComparer($sourceStorage, $configStorage, $configManager);
    if (!$storageComparer->createChangelist()->hasChanges()) {
      return FALSE;
    }
    return new ConfigImporter(
      $storageComparer,
      $container->get('event_dispatcher'),
      $container->get('config.manager'),
      $container->get('lock.persistent'),
      $container->get('config.typed'),
      $container->get('module_handler'),
      $container->get('module_installer'),
      $container->get('theme_handler'),
      $container->get('string_translation')
    );
  }

  /**
   * Remove any imported configurations.
   *
   * @AfterScenario
   */
  public function cleanImportedConfigs() {
    $configs = array_reverse($this->testConfigs);
    foreach ($configs as $config) {
      $this->getConfigImporter(FALSE, $config);
    }
    // Force rebuild the router.
    \Drupal::service('router.builder')->rebuild();

    $this->testConfigs = [];
  }

}
