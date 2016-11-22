<<<<<<< HEAD
# Shopify Embed Bundle
=======
# Shopify Special Offers Bundle (Embed)
>>>>>>> rleahy_ui

## Installation

** Install With Composer **

```json
{
   "require": {
<<<<<<< HEAD
       "sturpe/fgms-shopify-special-offers": "dev-master"
=======
       "sturple/fgms-shopify-special-offers": "dev-master"
>>>>>>> rleahy_ui
   }
}

```

and then execute

```json
$ composer update
```


## Configuration

<<<<<<< HEAD
**Add to ```app/AppKernal.php``` file**
=======
**Add to ```app/AppKernel.php``` file**
>>>>>>> rleahy_ui

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
<<<<<<< HEAD
}            

```


=======
}

```

## Requirements

64 bit PHP is required.  To check if you're running 64 bit PHP run the following code:

```php
<?php
echo PHP_INT_SIZE;
```

If `8` is printed you're running 64 bit PHP.
>>>>>>> rleahy_ui
