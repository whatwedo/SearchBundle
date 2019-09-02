# Configuration

There are two ways to configure the indexed entities. Either by using annotations or by using the `config.yml` file of your symfony application. It is also possible to mix both variants.

## Annotations

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

It is possible to define a custom formatter:

```
// src/Agency/UserBundle/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use whatwedo\SearchBundle\Annotation\Index;

// ...

    /**
     * @var string $createdAt
     * @ORM\Column(name="created_at", type="text")
     * @Index(formatter="whatwedo\CoreBundle\Formatter\DateTimeFormatter")
     */
    protected $createdAt;
    
// ...
```

You can index a method return value too:

```
// src/Agency/UserBundle/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use whatwedo\SearchBundle\Annotation\Index;

// ...

    /**
     * @return string
     * @Index()
     */
    public function getFullname()
    {
        return $this->firstname.' '.$this->lastname;
    }
    
// ...
```

Annotations for modifing the search results, with preSearch and postSearch hooks

```
// src/Agency/UserBundle/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use whatwedo\SearchBundle\Annotation\Index;
use whatwedo\SearchBundle\Annotation\Searchable;

// ...
/**
 * Personen.
 *
 * @ORM\Table(name="user")
 * @Searchable(
 *     preSearch="App\Agency\Search\UserPreSearch"
 *     preSearch="App\Agency\Search\UserPostSearch"
 * )
 */
class User
{
   // .....
    

```

The preSearch Hook 
```
// src/Agency/UserBundle/Search/UserPreSearch.php

// ...

use Doctrine\ORM\QueryBuilder;
use whatwedo\SearchBundle\Entity\PreSearchInterface;

// ...
class UserPreSearch implements PreSearchInterface
{
   // .....
    public function preSearch(QueryBuilder &$qb, string $query, ? string $entity, ? string $field): void
    {
        // modify the QueryBuilder
    }    

```


The postSearch Hook 
```
// src/Agency/UserBundle/Search/UserPostSearch.php

use whatwedo\SearchBundle\Entity\PostSearchInterface;

class PersonPostSearch implements PostSearchInterface
{


    public function postSearch(array $queryResults, string $query, ?string $entity, ?string $field): array
    {

        // modify queryResults
        $modifiedResults = [];

        foreach ($queryResults as $queryResult) {
            // remove special Entity 1
            if ($queryResult['foreignId'] == 1) {
                continue;
            }

            // remvoe low matchQuotes
            if ($queryResult['_matchQuote'] < 15) {
                continue;
            }

            $modifiedResults[] = $queryResult;
        }

        return $modifiedResults;
    }

}

```




## Configuration file

It's also possible to configure the indexed fields in your `config.yml` 
or create `config/packages/whatwedo_search.yaml` for Symfony 4

```
whatwedo_search:
    entities:
        user:
            class: Agency\UserBundle\Entity\User
            fields:
                - { name: firstname }
                - { name: createdAt, formatter: whatwedo\CoreBundle\Formatter\DateTimeFormatter }
```

