<?php
namespace App\Support\QueryBuilder;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedInclude;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class AllowedIncludeExtended extends AllowedInclude
{
    /**
     * Since Spatie query builder does not support the feature of customizing eagerloaded relationship,
     * we will implement our own by extending the AllowedInclude class for us to modify
     * the query.
     *
     * @param string $name
     * @param callable $callback
     * @return Collection
     */
    public static function relationshipBuilder(string $name, callable $callback) : Collection
    {
        return collect([
            new AllowedInclude($name, new class($callback) implements IncludeInterface {

                protected $callback;

                public function __construct(callable $callback)
                {
                    $this->callback = $callback;
                }

                public function __invoke(Builder $query, string $include)
                {
                    $callback = $this->callback;

                    return $query->with([
                        $include => $callback
                    ]);
                }
            })
        ]);
    }
}
