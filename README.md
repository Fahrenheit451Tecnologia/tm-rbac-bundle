# TM Rbac Bundle

## Prerequisites

- This only supports Doctrine ORM.


## Installation

1. Download TMRbacBundle using composer
2. Enable the bundle
3. Create your extending classes
4. Add mapping for Permission model
5. Add mapping for Role model
6. Add user trait and mapping to your user class
7. Configure the TMRbacBundle
8. Import TMRbacBundle routing
9. Update your database schema

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

### Step 3. Create your extending classes

You will need to extend the base Role and Permission classes.

```php
<?php
// src AppBundle/Entity/Permission
namespace AppBundle\Entity;

use TM\RbacBundle\Model\Permission as BasePermission;

class Permission extends BasePermission
{
    /**
     * @var string
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

```php
<?php
// src AppBundle/Entity/Role
namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\Role as BaseRole;

class Role extends BaseRole
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Collection|PermissionInterface[]
     */
    protected $permissions;
}
```

### Step 4. Add mapping for Permission model

You will then need to add your mapping to the concrete `Permission` class.
You will need to change the `AppBundle\Entity\Permission` to match the fully
qualified class name of your own `Permission` class.

**NOTE** The id mapping for this table is required to be of type `string`
with a generator strategy of `NONE`. ***This can not be changed***.

**NOTE** In this case we are naming the table `app_permission` but you can
name it anything you wish.

**NOTE** In this case we are using an auto-incremented integer for an id but you
can use anything you wish.

**NOTE** In this case we are using the `TM\RbacBundle\Repository\PermissionRepository`.
You are welcome to use your own repository but make sure that it implements the
`TM\RbacBundle\Repository\PermissionRepositoryInterface`

##### Using YAML

```yaml
# src/AppBundle/Resources/config/doctrine/Permission.orm.yml
AppBundle\Entity\Permission:
    name: tm_permission
    type: entity
    repositoryClass: AppBundle\Repository\PermissionRepository
    id:
        id:
            type: string
            length: 255
            generator:
                strategy: NONE
```

##### Using XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<!-- src/AppBundle/Resources/config/doctrine/User.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\Permission" table="tm_permission" repository-class="AppBundle\Repository\PermissionRepository">
        <id name="id" type="string" column="id" length="255">
            <generator strategy="NONE"/>
        </id>
    </entity>
</doctrine-mapping>
```

##### Using Annotations

```php
<?php
// src AppBundle/Entity/Permission
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TM\RbacBundle\Model\Permission as BasePermission;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PermissionRepository")
 * @ORM\Table(name="tm_permission")
 */
class Permission extends BasePermission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var string
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

### Step 5. Add mapping for Role model

You will then need to add your mapping to the concrete `Role` class.
You will need to change the `AppBundle\Entity\Role` to match the fully
qualified class name of your own `Role` class.

**NOTE** In this case we are naming the tables `tm_role` and `tm_roles_permissions`
(for the joining table) but you can name them anything you wish.

**NOTE** In this case we are using an auto-incremented integer for an id but you
can use anything you wish.

**NOTE** In this case we are using the `TM\RbacBundle\Repository\RoleRepository`.
You are welcome to use your own repository but make sure that it implements the
`TM\RbacBundle\Repository\RoleRepositoryInterface`

**NOTE** Please replace the target entity of the permissions property
(`AppBundle\Entity\Permission`) with your own `Permission` class name

##### Using YAML

```yaml
# src/AppBundle/Resources/config/doctrine/Role.orm.yml
AppBundle\Entity\Permission:
    name: tm_role
    type: entity
    repositoryClass: AppBundle\Repository\RoleRepository
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
    manyToMany:
        permissions:
            targetEntity: AppBundle\Entity\Permission
            joinTable:
                name: tm_roles_permissions
            joinColumns:
                role_id:
                    referencedColumnName: id
            inverseJoinColumns:
                permission_id:
                    referencedColumnName: id
```

##### Using XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<!-- src/AppBundle/Resources/config/doctrine/Role.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\Role" table="tm_role">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <many-to-many field="permissions" target-entity="AppBundle\Entity\Permission" repository-class="AppBundle\Repository\RoleRepository">
            <join-table name="tm_roles_permissions">
                <join-columns>
                    <join-column name="role_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="permission_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>
    </entity>
</doctrine-mapping>
```

##### Using Annotations

```php
<?php
// src AppBundle/Entity/Role
namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\Permission as BasePermission;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRepository")
 * @ORM\Table(name="tm_permission")
 */
class Permission extends BasePermission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Permission")
     * @ORM\JoinTable(
     *     name="tm_roles_permissions",
     *     joinColumns={@JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     *
     * @var Collection|PermissionInterface[]
     */
    protected $permissions;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

### Step 6. Add user trait and mapping to your user class

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. "Use" `UserTrait` in *your* `User` class.
2. Add `User` mapping to your mapping.

**NOTE** The doc uses a bundle named `AppBundle` according to the Symfony best
practices. However, you can of course place *your* `Role` class in the bundle
you want.

**NOTE** In this case we are naming the tables `tm_users_permissions` and `tm_users_roles_`
(for the joining tables) but you can name them anything you wish.

**NOTE** In this case we are using the `TM\RbacBundle\Repository\UserRepository`.
You are welcome to use your own repository but make sure that it implements the
`TM\RbacBundle\Repository\UserRepositoryInterface`

**NOTE** Please replace the target entity of the permissions property
(`AppBundle\Entity\Permission`) with your own `Permission` class name

"Use" `UserTrait` in *your* `User` class.

```php
<?php
// src/AppBundle/Entity/User
namespace AppBundle\Entity;

use TM\RbacBundle\Model\Traits\UserTrait as TMRbacUserTrait;
use TM\RbacBundle\Model\UserInterface as TMRbacUserInterface;

class User implements TMRbacUserInterface
{
    use TMRbacUserTrait;

    //...
}
```

"Use" `UserRepositoryTrait` in *your* `UserRepository` class.

```php
<?php
// src/AppBundle/Repository/UserRepository
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use TM\RbacBundle\Repository\Traits\UserRepositoryTrait as TMRbacUserRepositoryTrait;
use TM\RbacBundle\Repository\TMRbacUserRepositoryInterface;

class UserRepository extends EntityRepository implements TMRacUserRepositoryInterface
{
    use TMRbacUserRepositoryTrait;

    //...
}
```

Add `User` mapping to your mapping.

##### Using YAML

```yaml
# src/AppBundle/Resources/config/doctrine/User.orm.yml
AppBundle\Entity\User:
    repositoryClass: AppBundle\Reposotory\UserRepository
    //...
    manyToMany:
        userPermissions:
            targetEntity: AppBundle\Entity\Permission
            joinTable:
            	name: tm_users_permissions
                joinColumns:
                    user_id:
                        referencedColumnName: id
                inverseJoinColumns:
                    permission_id:
                        referencedColumnName: id
        userRoles:
            targetEntity: AppBundle\Entity\Role
            joinTable:
                name: tm_users_roles
                joinColumns:
                    user_id:
                        referencedColumnName: id
                inverseJoinColumns:
                    role_id:
                        referencedColumnName: id
```

##### Using XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<!-- src/AppBundle/Resources/config/doctrine/Role.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\User" table="tm_user" repository-class="AppBundle\Repository\UserRepository">
        <!-- The rest of your mapping -->

        <many-to-many field="userPermissions" target-entity="AppBundle\Entity\Permission">
            <join-table name="tm_users_permissions">
                <join-columns>
                    <join-column name="user_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="permission_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>

        <many-to-many field="userRoles" target-entity="AppBundle\Entity\Role">
            <join-table name="tm_users_roles">
                <join-columns>
                    <join-column name="user_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="role_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>
    </entity>
</doctrine-mapping>
```

##### Using Annotations

```php
<?php
// src AppBundle/Entity/User
namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\Permission as BasePermission;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\Traits\UserTrait as TMRbacUserTrait;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="tm_user")
 */
class User
{
    use TMRbacUserTrait;

    //...

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Permission")
     * @ORM\JoinTable(
     *     name="tm_users_permissions",
     *     joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     *
     * @var Collection|PermissionInterface[]
     */
    protected $userPermissions;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Role")
     * @ORM\JoinTable(
     *     name="tm_users_roles",
     *     joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     *
     * @var Collection|PermissionInterface[]
     */
    protected $userRoles;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

### Step 7. Configure the TMRbacBundle

```yaml
# app/config/config.yml

tm_rbac:
    models:
        # Add your permission model class name
        permission: AppBundle\Model\Permission
        # Add your user model class name
        role: AppBundle\Model\Role
        # Add your user model class name
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

### Step 8. Import TMRbacBundle routing

```yaml
# app/config/routing.yml
tm_rbac:
    resource: '@TMRbacBundle/Resources/config/routing.yml'
```
### Step 9. Update your database schema

```bash
$ php bin/console doctrine:schema:update --force
```

## Licence

This bundle is under the MIT license. See the complete license in [the bundle](https://github.com/Fahrenheit451Tecnologia/tm-rbac-bundle/blob/master/LICENSE)

## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/Fahrenheit451Tecnologia/tm-rbac-bundle/issues).