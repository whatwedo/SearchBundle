# Getting Started

This documentation provides a basic view of the possibilities of the whatwedoSearchBundle. 

## Requirements

This bundle has been tested on PHP >= 8.1 and Symfony >= 6.0. 
We don't guarantee that it works on lower versions.

## Installation

### Composer
The bundle depends on bootstrap icons. To get them running smoothly in your project
add this repository to you composer.json: ([Sadly composer cannot load repositories recursively](https://getcomposer.org/doc/faqs/why-cant-composer-load-repositories-recursively.md))
```json
"repositories": [
    {
        "type": "package",
        "package": {
            "name": "twbs/icons",
            "version": "1.8.1",
            "source": {
                "url": "https://github.com/twbs/icons",
                "type": "git",
                "reference": "tags/v1.8.1"
            }
        }
    }
]
```
Then the bundle to your dependencies and install it.

```
composer require whatwedo/search-bundle
```
**remove after relase**

The v3 version is still in developing,
so you need to add these lines manually to the `composer.json` `require` to get the version constraint right:
```json
    ...
    "whatwedo/search-bundle": "dev-3.0-dev as v3.0.0",
    ...
```
Run `composer update`  
After successfully installing the bundle, you should see changes in these files:
 - `composer.json`
 - `composer.lock`
 - `package.json`
 - `symfony.lock`
 - `assets/controllers.json`
 - `assets/bundles.php`

### ORM
Doctrine does not support `MATCH AGAINST` per default. You can enable it by adding the following lines to your `config/packages/doctrine.yaml`

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
use Doctrine\ORM\Mapping as ORM;
use whatwedo\SearchBundle\Annotation\Index;

#[ORM\Entity]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Index]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Index]
    private $description;
    
// ...
```

Now and after every database change which are **not** performed by Doctrine, you have to update your index.

Read more about populating your [here](indexing.md)

```
php bin/console whatwedo:search:populate
```

Now you can use the Index repository to search in your entities

```
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Repository\IndexRepository;

class DefaultController extends AbstractController
{

    #[Route('/', name: 'default')]
    public function searchAction(IndexRepository $indexRepository, PostRepository $postRepository, ManagerRegistry $doctrine)
    {
        // obtain query somehow
        $query = '...';

        // search specific entity class and map them to their object
        $postIds = $indexRepository->search($query, Post::class);
        $posts = $postRepository
            ->createQueryBuilder('post')
            ->where('post.id IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()
            ->getResult()
        ;

        // search all entity classes and map them to their object
        $allIds = $indexRepository->searchEntities($query);
        $all = array_map(function (array $result) use ($doctrine) {
            return $doctrine
                ->getRepository($result['model'])
                ->find($result['id'])
            ;
        }, $allIds);
        
    }
}
```

That's it!

