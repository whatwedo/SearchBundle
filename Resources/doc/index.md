# Getting Started

This documentation provides a basic view of the possibilities of the whatwedoSearchBundle. 
The documentation will be extended while developing the bundle.

## Requirements

This bundle has been tested on PHP >= 7.0 and Symfony >= 3.0. 
We don't guarantee that it works on lower versions.


## Installation

First, add the bundle to your dependencies and install it.

```
composer require whatwedo/search-bundle
```

Secondly, enable this bundle and the whatwedoSearchBundle in your kernel.

```
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new whatwedo\SearchBundle\whatwedoSearchBundle(),
        // ...
    );
}
```

Doctrine does not support `MATCH AGAINST` per default. You can enable the it by adding the following lines to your `config.yml`

```
doctrine:
    orm:
        dql:
            string_functions:
                MATCH_AGAINST: whatwedo\SearchBundle\Extension\Doctrine\Query\Mysql\MatchAgainst
```

Next, update your database schema.

```
php bin/console doctrine:schema:update --force
```


## Use the bundle

In your entities, you have to configure the indexed fields with the index annotation:

```
// src/Agency/UserBundle/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use whatwedo\SearchBundle\Annotation\Index;

// ...

    /**
     * @var string $firstname
     * @ORM\Column(name="firstname", type="text"0)
     * @Index()
     */
    protected $firstname;
    
// ...
```

Now and after every database change which are not performed by Doctrine, you have to update your index.

```
bin/console whatwedo:search:populate
```

Now you can use the Index repository to search in your entities

```
// src/Agency/UserBundle/Controller/UserController.php

// ...
    public function searchAction($query)
    {
        // Get all id's of entities containing the query string
        $ids = $this->em->getRepository('whatwedoSearchBundle:Index')->search($query, User::class);
        
        // Map users
        $users $this->em->getRepository('AgencyUserBundle:User')
            ->createQueryBuilder('u')
            ->where('u.id IN (:ids)')->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    
        // Return view
        return $this->render('search.html.twig', [
            'users' => $users,
        ]);
    }
    
// ...
```


That's it!

### More resources

- [Configuration](configuration.md)
