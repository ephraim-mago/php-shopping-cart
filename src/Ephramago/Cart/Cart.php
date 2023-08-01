<?php

namespace Ephramago\Cart;

use Ephramago\Cart\Helpers\Helpers;
use Ephramago\Cart\Exceptions\InvalidItemException;
use Ephramago\Cart\Contracts\Session\SessionInterface;

/**
 * Class Cart
 * @package Ephramago\Cart
 */
class Cart
{
    /**
     * the item storage
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * the cart session key
     *
     * @var string
     */
    protected $instanceName;

    /**
     * the session key use for the cart
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * the session key use to persist cart items
     *
     * @var string
     */
    protected $sessionKeyCartItems;

    /**
     * This holds the currently added item id in cart for association
     * 
     * @var string
     */
    protected $currentItemId;

    /**
     * Configuration to pass to ItemCollection
     *
     * @var array
     */
    protected $config;

    /**
     * our object constructor
     *
     * @param SessionInterface $session
     * @param string $instanceName
     * @param string $session_key
     * @param array $config
     */
    public function __construct($session, $instanceName, $session_key, $config)
    {
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKey = $session_key;
        $this->sessionKeyCartItems = $this->sessionKey . '_cart_items';
        $this->config = $config;
    }

    /**
     * sets the session key
     *
     * @param string  $sessionKey the session key or identifier
     * @return $this|bool
     * @throws \Exception
     */
    public function session($sessionKey)
    {
        if (!$sessionKey) throw new \Exception("Session key is required.");

        $this->sessionKey = $sessionKey;
        $this->sessionKeyCartItems = $sessionKey . '_cart_items';

        return $this;
    }

    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }

    /**
     * get an item on a cart by item ID
     *
     * @param string|int  $itemId
     * @return mixed
     */
    public function get($itemId)
    {
        return $this->getContent()->get($itemId);
    }

    /**
     * check if an item exists by item ID
     *
     * @param string|int  $itemId
     * @return bool
     */
    public function has($itemId)
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * add item to the cart, it can be an array or multi dimensional array
     *
     * @param string|int|array  $id
     * @param string  $name
     * @param float  $price
     * @param int  $quantity
     * @param mixed  $associatedModel
     * @return $this
     * @throws InvalidItemException
     */
    public function add($id, $name = null, $price = null, $quantity = null, $associatedModel = null)
    {
        if (is_array($id)) {
            if (Helpers::isMultiArray($id)) {
                foreach ($id as $item) {
                    $this->add(
                        $item['id'],
                        $item['name'],
                        $item['price'],
                        $item['quantity'],
                        Helpers::issetAndHasValueOrAssignDefault($item['associatedModel'], null)
                    );
                }
            } else {
                $this->add(
                    $id['id'],
                    $id['name'],
                    $id['price'],
                    $id['quantity'],
                    Helpers::issetAndHasValueOrAssignDefault($id['associatedModel'], null)
                );
            }

            return $this;
        }

        $data = array(
            'id' => $id,
            'name' => $name,
            'price' => Helpers::normalizePrice($price),
            'quantity' => $quantity,
        );

        if (isset($associatedModel) && $associatedModel != '') {
            $data['associatedModel'] = $associatedModel;
        }

        // get the cart
        $cart = $this->getContent();

        // if the item is already in the cart we will just update it
        if ($cart->has($id)) {
            $this->update($id, $data);
        } else {
            $this->addRow($id, $data);
        }

        $this->currentItemId = $id;

        return $this;
    }

    /**
     * update a cart
     *
     * @param string|int  $id
     * @param array  $data
     *
     * the $data will be an associative array, you don't need to pass all the data, only the key value
     * of the item you want to update on it
     * @return bool
     */
    public function update($id, $data)
    {
        $cart = $this->getContent();

        $item = $cart->pull($id);

        foreach ($data as $key => $value) {
            if ($key == 'quantity') {
                $item = $this->updateQuantityRelative($item, $key, $value);
            } else {
                $item[$key] = $value;
            }
        }

        $cart->put($id, $item);

        $this->save($cart);

        return true;
    }

    /**
     * removes an item on cart by item ID
     *
     * @param string|int  $id
     * @return bool
     */
    public function remove($id)
    {
        $cart = $this->getContent();

        $cart->forget($id);

        $this->save($cart);

        return true;
    }

    /**
     * clear cart items
     * 
     * @return bool
     */
    public function clear()
    {
        $this->session->put(
            $this->sessionKeyCartItems,
            array()
        );

        return true;
    }

    /**
     * the new total in which conditions are already applied
     *
     * @return float
     */
    public function getTotal()
    {
        $cart = $this->getContent();

        $sum = array_reduce($cart->all(), function ($a, ItemCollection $item) {
            return $a += $item->getPrice(false);
        }, 0);

        return Helpers::formatValue(floatval($sum), $this->config['format_numbers'], $this->config);
    }

    /**
     * get total quantity of items in the cart
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        $items = $this->getContent();

        if ($items->isEmpty()) return 0;

        $count = array_reduce($items->all(), function ($a, ItemCollection $item) {
            return $a += $item->quantity;
        }, 0);

        return $count;
    }

    /**
     * check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getContent()->isEmpty();
    }

    /**
     * add row to cart collection
     *
     * @param string|int  $id
     * @param array  $item
     * @return bool
     */
    protected function addRow($id, $item)
    {
        $cart = $this->getContent();

        $cart->put($id, new ItemCollection($item, $this->config));

        $this->save($cart);

        return true;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return new CartCollection($this->session->get($this->sessionKeyCartItems));
    }

    /**
     * save the cart
     *
     * @param CartCollection $cart
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKeyCartItems, $cart);
    }

    /**
     * update a cart item quantity relative to its current quantity
     *
     * @param array  $item
     * @param string|int  $key
     * @param mixed  $value
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if (preg_match('/\-/', $value) == 1) {
            $value = (int)str_replace('-', '', $value);

            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', $value) == 1) {
            $item[$key] += (int)str_replace('+', '', $value);
        } else {
            $item[$key] += (int)$value;
        }

        return $item;
    }
}
