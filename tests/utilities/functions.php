<?php

function create($class, $attributes = [], $times = null, $states = null)
{
    if ($states) {
        return factory($class, $times)->states($states)->create($attributes);
    }

    return factory($class, $times)->create($attributes);
}

function make($class, $attributes = [], $times = null, $states = null)
{
    if ($states) {
        return factory($class, $times)->states($states)->make($attributes);
    }

    return factory($class, $times)->make($attributes);
}
