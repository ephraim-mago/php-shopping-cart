<?php

namespace Ephramago\Tests\Core;

use Ephramago\Cart\Contracts\Session\SessionInterface;

class Session implements SessionInterface
{
    protected $session = [];

    /**
     * Checks if a key is present and not null.
     *
     * @param  string|int $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->session[$key]);
    }

    /**
     * Get an item from the session.
     *
     * @param  string|int $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return (isset($this->session[$key])) ? $this->session[$key] : $default;
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return void
     */
    public function put($key, $value)
    {
        $this->session[$key] = $value;
    }

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string  $key
     * @return void
     */
    public function delete($key)
    {
        unset($this->session[$key]);
    }
}
