# Custom Filter Plugin

For cakePHP 1.3

This module add controls to your paginated view to filter result on any field

## Installation

1. Put the content of this plugin in "app/plugins/" in a folder named "custom_filter".
2. Run "database.sql" in the database.

## Getting started

In the Controller
```php
	var $components = array('CustomFilter.CustomFilter');
```

In the model

```php
var $actsAs = array('CustomFilter.CustomFiltered');
```

In the view

```php
<?php echo $this->CustomFilter->filters(); ?>
```
