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

The following configuration options may/must be set in `config.yml`:

```yaml
fgms_special_offers:
    api_key:            # API key for Shopify
    secret:             # Secret for Shopify
    expired:            # Number of expired special offers shown on main page of app
    notifications:      # Optional: Only specify if you want email notifications on sale started/ended
        from:           # Specifies email address email notifications will come from
            name:       # Optional, full name of person email will come from
            address:    # Email address of person email will come from
        to:             # Specifies email addresses email notifications will be sent to
            - name:     # Optional, full name of person email will be sent to
              address:  # Email address of person email will be sent to
        start_template: # Twig template which will be rendered for special offer started
        end_template:   # Twig template which will be rendered for special offer ended
```

## Requirements

64 bit PHP is required.  To check if you're running 64 bit PHP run the following code:

```php
<?php
echo PHP_INT_SIZE;
```

If `8` is printed you're running 64 bit PHP.

## Shopify App Configuration

The bundle specifies the following routes which must be known to configure as a Shopify App:

- **Install:** `/install`
- **OAuth:** `/auth`
- **Home:** `/`
