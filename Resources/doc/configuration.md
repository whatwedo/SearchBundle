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

## Configuration file

It's also possible to configure the indexed fields in your `config.yml`.

```
whatwedo_search:
    entities:
        user:
            class: Agency\UserBundle\Entity\User
            fields:
                - { name: firstname }
                - { name: createdAt, formatter: whatwedo\CoreBundle\Formatter\DateTimeFormatter }
```
