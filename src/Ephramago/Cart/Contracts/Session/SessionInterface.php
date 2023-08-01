<?php

namespace Ephramago\Cart\Contracts\Session;

interface SessionInterface
{
    /**
     * Checks if a key is present and not null.
     *
     * @param  string|int  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get an item from the session.
     *
     * @param  string|int  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return void
     */
    public function put($key, $value);

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string|int  $key
     * @return void
     */
    public function delete($key);
}
