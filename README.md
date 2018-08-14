# Behat Drupal Extensions
Some useful extensions to the contexts available in drupal/drupal-extension and behatch/contexts:

The main features are:
* Create nodes ordered by date: node creation is really fast, within milliseconds, so the timestamps
  will be the same for multiple nodes. This allows you to create nodes with unique creation and changed dates.
* Fetch nodes as JSON by internal or url alias directly.
* Create menu items.
* Sitemap and GoogleNews validation.

# Installation
Add this repo to the list of repositories in your composer.json file:

```
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/remko79/behat-drupal-extensions.git"
    }
  ]
```

Then install it: `composer require remko79/behat-drupal-extensions`.

# Example
An example are added in the `example` directory.
