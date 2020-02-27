# HandcraftedInTheAlps - SuluResourceBundle

## Installation

### Add repository to `composer.json`

```bash
composer require handcraftedinthealps/sulu-resource-bundle
```

### Register Bundle in `config/bundles.php`

```php
<?php

return [
    /* ... */
    HandcraftedInTheAlps\Bundle\SuluResourceBundle\HandcraftedInTheAlpsSuluResourceBundle::class => ['all' => true],
];
```
