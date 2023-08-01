# Php Shopping Cart

[![Total Downloads](http://poser.pugx.org/ephra/shopping-cart/downloads)](https://packagist.org/packages/ephra/shopping-cart)

Shopping Cart is an implementation allowing to add the shopping cart part in your PHP applications

This package is an adaptation, it is based on and uses several functions on the package Darrydecode Laravel Shopping Cart :
https://github.com/darryldecode/laravelshoppingcart

## INSTALLATION

Install the package through [Composer](http://getcomposer.org/).

`composer require ephra/shopping-cart`

## CONFIGURATION

1. Create file PhpSession.php is implemente SessionInterface.

```php
Ephramago\Cart\Contracts\Session\SessionInterface::class
```

2. Create file CartConfig.php is return the configuration array arguments

```php
  return [
    'format_numbers' => false,
    'decimals' => 2,
    'dec_point' => '.',
    'thousands_sep' => ',',
]
```

## HOW TO USE

- [Quick Usage](#usage-usage-example)
- [Usage](#usage)
- [License](#license)

## Quick Usage Example

```php
// Quick Usage with the Product Model Association & User session binding

$Product = Product::find($productId); // assuming you have a Product model with id, name, description & price
$rowId = 456; // generate a unique() row ID
$userID = 2; // the user ID to bind the cart contents
$phpSession = new PhpSession() // create new instance PhpSession
$cartConfig = require('CartConfig.php') // receive the config array in the file

// create new instance of cart
$cart = new Cart($phpSession, "shopping", $userID, $cartConfig)

// add the product to cart
$cart->session($userID)->add(array(
    'id' => $rowId,
    'name' => $Product->name,
    'price' => $Product->price,
    'quantity' => 4,
    'associatedModel' => $Product
));

// update the item on cart
$cart->session($userID)->update($rowId,[
	'quantity' => 2,
	'price' => 98.67
]);

// delete an item on cart
$cart->session($userID)->remove($rowId);

// view the cart items
$items = $cart->getContent();
foreach($items as $row) {

	echo $row->id; // row ID
	echo $row->name;
	echo $row->qty;
	echo $row->price;

	echo $item->associatedModel->id; // whatever properties your model have
  	echo $item->associatedModel->name; // whatever properties your model have
  	echo $item->associatedModel->description; // whatever properties your model have
}

// FOR FULL USAGE, SEE BELOW..
```

## Usage

### IMPORTANT NOTE!

By default, the cart has a default sessionKey that holds the cart data. This
also serves as a cart unique identifier which you can use to bind a cart to a specific user.
To override this default session Key, you will just simply call the `$cart->session($sessionKey)` method
BEFORE ANY OTHER METHODS!!.

Example:

```php
$userId // the current login user id

// This tells the cart that we only need or manipulate
// the cart data of a specific user. It doesn't need to be $userId,
// you can use any unique key that represents a unique to a user or customer.
// basically this binds the cart to a specific user.
$cart->session($userId);

// then followed by the normal cart usage
$cart->add();
$cart->update();
$cart->remove();
$cart->getTotal();
// and so on..
```

See More Examples below:

Adding Item on Cart: **Cart::add()**

There are several ways you can add items on your cart, see below:

```php
/**
 * add item to the cart, it can be an array or multi dimensional array
 *
 * @param string|array $id
 * @param string $name
 * @param float $price
 * @param int $quantity
 * @param mixed $associatedModel
 * @return $this
 * @throws InvalidItemException
 */

 # ALWAYS REMEMBER TO BIND THE CART TO A USER BEFORE CALLING ANY CART FUNCTION
 # SO CART WILL KNOW WHO'S CART DATA YOU WANT TO MANIPULATE. SEE IMPORTANT NOTICE ABOVE.
 # EXAMPLE: $cart->session($userId); then followed by cart normal usage.

 # NOTE:
 # the 'id' field in adding a new item on cart is not intended for the Model ID (example Product ID)
 # instead make sure to put a unique ID for every unique product or product that has it's own unique prirce,
 # because it is used for updating cart and how each item on cart are segregated during calculation and quantities.
 # You can put the model_id instead as an attribute for full flexibility.
 # Example is that if you want to add same products on the cart but with totally different attribute and price.
 # If you use the Product's ID as the 'id' field in cart, it will result to increase in quanity instead
 # of adding it as a unique product with unique attribute and price.

// Simplest form to add item on your cart
Cart::add(455, 'Sample Item', 100.99, 2, array());

// array format
Cart::add(array(
    'id' => 456, // inique row ID
    'name' => 'Sample Item',
    'price' => 67.99,
    'quantity' => 4,
    'associatedModel' => $Product
));

// add multiple items at one time
Cart::add(array(
  array(
      'id' => 456,
      'name' => 'Sample Item 1',
      'price' => 67.99,
      'quantity' => 4,
      'associatedModel' => $Product
  ),
  array(
      'id' => 568,
      'name' => 'Sample Item 2',
      'price' => 69.25,
      'quantity' => 4,
      'associatedModel' => $Product
  ),
));

// add cart items to a specific user
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->add(array(
    'id' => 456, // inique row ID
    'name' => 'Sample Item',
    'price' => 67.99,
    'quantity' => 4,
    'associatedModel' => $Product
));

// NOTE:
// Please keep in mind that when adding an item on cart, the "id" should be unique as it serves as
// row identifier as well. If you provide same ID, it will assume the operation will be an update to its quantity
// to avoid cart item duplicates
```

Updating an item on a cart: **Cart::update()**

Updating an item on a cart is very simple:

```php
/**
 * update a cart
 *
 * @param $id (the item ID)
 * @param array $data
 *
 * the $data will be an associative array, you don't need to pass all the data, only the key value
 * of the item you want to update on it
 */

$cart->update(456, array(
  'name' => 'New Item Name', // new item name
  'price' => 98.67, // new item price, price can also be a string format like so: '98.67'
));

// you may also want to update a product's quantity
$cart->update(456, array(
  'quantity' => 2, // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
));

// you may also want to update a product by reducing its quantity, you do this like so:
$cart->update(456, array(
  'quantity' => -1, // so if the current product has a quantity of 4, it will subtract 1 and will result to 3
));

// NOTE: as you can see by default, the quantity update is relative to its current value
// if you want to just totally replace the quantity instead of incrementing or decrementing its current quantity value
// you can pass an array in quantity value like so:
$cart->update(456, array(
  'quantity' => 5
  ),
));
// so with that code above as relative is flagged as false, if the item's quantity before is 2 it will now be 5 instead of
// 5 + 2 which results to 7 if updated relatively..

// updating a cart for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
$cart->session($userId)->update(456, array(
  'name' => 'New Item Name', // new item name
  'price' => 98.67, // new item price, price can also be a string format like so: '98.67'
));
```

Removing an item on a cart: **Cart::remove()**

Removing an item on a cart is very easy:

```php
/**
 * removes an item on cart by item ID
 *
 * @param $id
 */

$cart->remove(456);

// removing cart item for a specific user's cart
$userId = auth()->user()->id; // or any string represents user identifier
$cart->session($userId)->remove(456);
```

Getting an item on a cart: **Cart::get()**

```php

/**
 * get an item on a cart by item ID
 * if item ID is not found, this will return null
 *
 * @param $itemId
 * @return null|array
 */

$itemId = 456;

Cart::get($itemId);

// You can also get the sum of the Item multiplied by its quantity, see below:
$summedPrice = $cart->get($itemId)->getPrice();

// get an item on a cart by item ID for a specific user's cart
$userId = auth()->user()->id; // or any string represents user identifier
$cart->session($userId)->get($itemId);
```

Getting cart's contents and count: **Cart::getContent()**

```php

/**
 * get the cart
 *
 * @return CartCollection
 */

$cartCollection = $cart->getContent();

// NOTE: Because cart collection
// See some of its method below:

// count carts contents
$cartCollection->count();

// transformations
$cartCollection->toArray();
$cartCollection->toJson();

// Getting cart's contents for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
$cart->session($userId)->getContent($itemId);
```

Check if cart is empty: **Cart::isEmpty()**

```php
/**
* check if cart is empty
*
* @return bool
*/
$cart->isEmpty();

// Check if cart's contents is empty for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
$cart->session($userId)->isEmpty();
```

Get cart total quantity: **Cart::getTotalQuantity()**

```php
/**
* get total quantity of items in the cart
*
* @return int
*/
$cartTotalQuantity = $cart->getTotalQuantity();

// for a specific user
$cartTotalQuantity = $cart->session($userId)->getTotalQuantity();
```

Get cart total: **Cart::getTotal()**

```php
/**
 * the new total in which conditions are already applied
 *
 * @return float
 */
$total = $cart->getTotal();

// for a specific user
$total = $cart->session($userId)->getTotal();
```

Clearing the Cart: **Cart::clear()**

```php
/**
* clear cart
*
* @return void
*/
$cart->clear();
$cart->session($userId)->clear();
```

## License

The Laravel Shopping Cart is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
