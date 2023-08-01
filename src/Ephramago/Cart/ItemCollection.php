<?php

namespace Ephramago\Cart;

use Ephramago\Cart\Helpers\Helpers;

class ItemCollection extends CartCollection
{
    /**
     * Sets the config parameters.
     *
     * @var
     */
    protected $config;

    /**
     * ItemCollection constructor.
     * @param array|mixed $items
     * @param $config
     */
    public function __construct($items, $config = [])
    {
        parent::__construct($items);

        $this->config = $config;
    }

    public function __get($name)
    {
        if ($this->has($name) || $name == 'model') {
            return !is_null($this->get($name)) ? $this->get($name) : $this->getAssociatedModel();
        }
        return null;
    }

    /**
     * return the associated model of an item
     *
     * @return bool
     */
    protected function getAssociatedModel()
    {
        if (!$this->has('associatedModel')) {
            return false;
        }

        $associatedModel = $this->get('associatedModel');

        return new $associatedModel() ?: false;
    }

    public function getPrice($formatted = true)
    {
        return Helpers::formatValue($this->price * $this->quantity, $formatted, $this->config);
    }
}
