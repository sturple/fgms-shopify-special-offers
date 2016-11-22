# Shopify Special Offers Bundle (Embed)

## Installation

** Install With Composer **

```json
{
   "require": {
       "sturple/fgms-shopify-special-offers": "dev-master"
   }
}

```

and then execute

```json
$ composer update
```


## Configuration

**Add to ```app/AppKernel.php``` file**

```php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
             new Fgms\SpecialOffersBundle\FgmsSpecialOffersBundle();
        ]
    }
}

```

## Requirements

64 bit PHP is required.  To check if you're running 64 bit PHP run the following code:

```php
<?php
echo PHP_INT_SIZE;
```

If `8` is printed you're running 64 bit PHP.
