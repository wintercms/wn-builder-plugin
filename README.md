# Builder plugin

A user-friendly, visual plugin scaffolding tool that makes it trivial to get a new plugin running in Winter CMS. This plugin takes away the leg-work in setting up the necessary structure for a plugin by providing several simple tools that manage all facets of your plugin's definitions and files.

## Features

With the Builder plugin, you can:

- Create and update your plugin's database schema, with automatic migration generation.
- Define models for your database tables.
- Create and manage controllers.
- Define your plugin's Backend navigation items and available permissions.
- Design and create your forms and lists.
- Manage your plugin's versions and migrations.
- Use simple, universal Builder components on your CMS templates to display full lists and single records.

The Builder plugin works gracefully with your own work tools and methodologies, being both comprehensive and yet careful with its own scope of plugin modifications - allowing you to work on more complex plugin functionality in your own development environment without interference from the Builder plugin.

## Installation

You can install the plugin using Composer. As this is a development plugin, it should be defined as a "dev" dependency.

```
composer require --dev wintercms/wn-builder-plugin
```
