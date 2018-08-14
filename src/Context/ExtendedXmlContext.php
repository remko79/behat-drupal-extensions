<?php

namespace Behat\remko79\Context;

use Behatch\Context\XmlContext;

/**
 * Extends the default XmlContext with extra features.
 */
class ExtendedXmlContext extends XmlContext {

  /**
   * Validate against GoogleNews 0.9 xsd.
   *
   * @Then the Google News feed should be valid
   */
  public function theGoogleNewsFeedShouldBeValid() {
    $this->theXmlFeedShouldBeValidAccordingToTheXsd(__DIR__ . '/../../schemas/xsd/googlenews.xsd');
  }

  /**
   * Validate against sitemap 0.9 xsd.
   *
   * @Then the XML sitemap should be valid
   */
  public function theXmlSitemapShouldBeValid() {
    $this->theXmlFeedShouldBeValidAccordingToTheXsd(__DIR__ . '/../../schemas/xsd/sitemap.xsd');
  }

}
