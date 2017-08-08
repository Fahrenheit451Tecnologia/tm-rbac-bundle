# TM Rbac Bundle

## Prerequisites

- This only supports Doctrine ORM.


## Installation

1. Download TMRbacBundle using composer
2. Enable the bundle
3. Add user trait and mapping to your user class
4. Configure the TMRbacBundle
5. Import TMRbacBundle routing
6. Update your database schema

### Step 1. Download TMRbacBundle using composer

Add this repository to your composer.json

```javascript
// composer.json
{
    //..
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Fahrenheit451Tecnologia/tm-rbac-bundle"
        }
    ],
    //...
}
```

Require the bundle using the command line

```cli
$ composer require timemanager/rbac-bundle
```

### Step 2. Enable the bundle


```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new TM\RbacBundle\TMRbacBundle(),
        // ...
    );
}
```

### Step 3. Add user trait and mapping to your user class

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. "Use" `UserTrait` in *your* `User` class.
2. Add `User` mapping to your mapping.

**NOTE** The doc uses a bundle named `AppBundle` according to the Symfony best
practices. However, you can of course place *your* `Role` class in the bundle
you want.

"Use" `UserTrait` in *your* `User` class.

```php
<?php
// src/AppBundle/Entity/User
namespace AppBundle\Entity;

use TM\RbacBundle\Model\Traits\UserTrait as TMRbacUserTrait;

class User
{
    use TMRbacUserTrait;

    //...
}
```

Add `User` mapping to your mapping.

```yaml
# src/AppBundle/Resources/config/doctrine/User.orm.yml
AppBundle\Entity\User:
    //...
    manyToMany:
        userPermissions:
            targetEntity: TM\RbacBundle\Model\Permission
            joinTable:
            	name: tm_rbac_user_permissions
                joinColumns:
                    user_id:
                        referencedColumnName: id
                inverseJoinColumns:
                    permission_id:
                        referencedColumnName: id
        userRoles:
            targetEntity: TM\RbacBundle\Model\Role
            joinTable:
                name: tm_rbac_user_roles
                joinColumns:
                    user_id:
                        referencedColumnName: id
                inverseJoinColumns:
                    role_id:
                        referencedColumnName: id
```

### Step 4. Configure the TMRbacBundle

```yaml
# app/config/config.yml

tm_rbac:
    models:
        # Add you user model class name
        user: AppBundle\Model\User
    listeners:
        # Set whether you want to use the permissions listener to listen for
        # controller actions using the @Permission annotiation
        permission: true|false
    permissions:
        # list all of your permissions in a key => value format
        # permissions keys can contain only lower case characters and underscores
        a_permission: A Permission
        a_different_permission: A Different Permission
        //...
```

### Step 5. Import TMRbacBundle routing

```yaml
# app/config/routing.yml
tm_rbac:
    resource: '@TMRbacBundle/Resources/config/routing.yml'
```
### Step 6. Update your database schema

```bash
$ php bin/console doctrine:schema:update --force
```

## Licence

This bundle is under the MIT license. See the complete license in [the bundle](https://github.com/Fahrenheit451Tecnologia/tm-rbac-bundle/blob/master/LICENSE)

## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/Fahrenheit451Tecnologia/tm-rbac-bundle/issues).