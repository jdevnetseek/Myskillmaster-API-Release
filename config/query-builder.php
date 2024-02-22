<?php

return [

    /*
     * By default the package will use the `include`, `filter`, `sort` and `fields` query parameters.
     *
     * Here you can customize those names.
     */
    'parameters' => [
        'include' => 'include',

        'filter' => 'filter',

        'sort' => 'sort',

        'fields' => 'fields',

        'append' => 'append',
    ],

    /*
     * Related model counts are included using the relationship name suffixed with this string.
     * For example: GET /users?include=postsCount
     */
    'count_suffix' => 'Count',

];
