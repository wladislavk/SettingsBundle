About
=====

This is a simple bundle that aims to simplify the process of getting Symfony project settings from
different places. Currently it supports two sources of settings: a config file or an entity under
control of Doctrine. Note that this bundle requires Doctrine to work.

Installation
============

The only thing you need to do prior to using this bundle is to define the entity. If you will
always be using parameter-based settings, you can skip this completely.

To create an entity supported by this bundle, you need to create a Doctrine entity class that
would implement VKR\SettingsBundle\Interfaces\SettingsEntityInterface and define two of its
methods, *getName()* and *getValue()*, both of those must return strings.

Then, you need to create a parameter called *settings_entity* in your config file, it must
contain the fully qualified name of your entity, e.g.

```settings_entity: 'AppBundle\Entity\Settings'```

Please note that the following entry will NOT work:

```settings_entity: 'AppBundle:Settings'```

That's it.

Usage
=====

First, create a *SettingsRetriever* service object. Then, use its *get()* method with your
setting name as an argument. The script will first try to get a parameter with that key
from your config file. If there is none, it will try to find a DB record with name parameter
equal to *get()* method's argument and then use *getValue()* on it. Otherwise, it will through
a *VKR\SettingsBundle\Exception\SettingNotFoundException*.

Example (should be run from a controller):
```
$settingsRetriever = $this->get('vkr_settings.settings_retriever');
try {
    $mySetting = $settingsRetriever->get('my_setting');
} catch (VKR\SettingsBundle\Exception\SettingNotFoundException $e) {
    // do something
}
```

API
===

*void SettingsRetriever::__construct(Container $container, EntityManager $em)*

Container and entity manager should be injected if initialized manually

*string SettingsRetriever::get(string $settingName, bool $suppressErrors=false)*

If the second argument is set to true, the method will return false if the setting is not found
instead of throwing an exception.

*SettingsEntityInterface[] SettingsRetriever::getAllFromDB()*

Will try to retrieve all objects from the settings entity as a key-value array.
If the entity is not set, will return empty array. This method will ignore all parameters-based
settings.
