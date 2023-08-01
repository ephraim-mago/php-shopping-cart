<?php

namespace Ephramago\Tests;

use Ephramago\Cart\Cart;
use PHPUnit\Framework\TestCase;
use Ephramago\Tests\Core\Session;
use Ephramago\Cart\Contracts\Session\SessionInterface;

class CartTest extends TestCase
{
    /**
     *
     * @var Cart
     */
    protected $cart;

    public function setUp(): void
    {
        /* $config = [
            'format_numbers' => false,
            'decimals' => 2,
            'dec_point' => '.',
            'thousands_sep' => ',',
        ]; */

        $this->cart = new Cart(new Session(), "shopping", "SAMPLESESSIONKEY", require(__DIR__ . '/Core/configMock.php'));
    }

    public function test_session_class_instance_of_session_interface()
    {
        $session = new Session();

        $this->assertInstanceOf(SessionInterface::class, $session);
    }

    public function test_cart_can_add_item()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, new \stdClass());

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart content should be 1');
        $this->assertEquals(455, $this->cart->getContent()->first()['id'], 'Item added has ID of 455 so first content ID should be 455');
        $this->assertEquals(100.99, $this->cart->getContent()->first()['price'], 'Item added has price of 100.99 so first content price should be 100.99');
    }

    public function test_cart_can_add_items_as_array()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 67.99,
            'quantity' => 4,
            'associatedModel' => new \stdClass()
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart should have 1 item on it');
        $this->assertEquals(456, $this->cart->getContent()->first()['id'], 'The first content must have ID of 456');
        $this->assertEquals('Sample Item', $this->cart->getContent()->first()['name'], 'The first content must have name of "Sample Item"');
    }

    public function test_cart_can_add_items_with_multidimensional_array()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 4,
                'associatedModel' => new \stdClass()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 4,
                'associatedModel' => new \stdClass()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 4,
                'associatedModel' => new \stdClass()
            ),
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertCount(3, $this->cart->getContent()->toArray(), 'Cart should have 3 items');
    }

    public function test_cart_can_add_item_without_associatedModel()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
    }

    public function test_cart_update_existing_item()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
            ),
        );

        $this->cart->add($items);

        $itemIdToEvaluate = 456;

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Sample Item 1', $item['name'], 'Item name should be "Sample Item 1"');
        $this->assertEquals(67.99, $item['price'], 'Item price should be "67.99"');
        $this->assertEquals(3, $item['quantity'], 'Item quantity should be 3');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(456, array(
            'name' => 'Renamed',
            'quantity' => 2,
            'price' => 105,
        ));

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Renamed', $item['name'], 'Item name should be "Renamed"');
        $this->assertEquals(105, $item['price'], 'Item price should be 105');
        $this->assertEquals(5, $item['quantity'], 'Item quantity should be 2');
    }

    public function test_clearing_cart()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
            ),
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty(), 'prove first cart is not empty');

        // now let's clear cart
        $this->cart->clear();

        $this->assertTrue($this->cart->isEmpty(), 'cart should now be empty');
    }

    public function test_cart_get_total_quantity()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
            ),
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty(), 'prove first cart is not empty');

        // now let's count the cart's quantity
        $this->assertIsInt($this->cart->getTotalQuantity(), 'Return type should be INT');
        $this->assertEquals(4, $this->cart->getTotalQuantity(), 'Cart\'s quantity should be 4.');
    }

    public function test_cart_can_add_items_as_array_with_associated_model()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 67.99,
            'quantity' => 4,
            'associatedModel' => new \stdClass()
        );

        $this->cart->add($item);

        $addedItem = $this->cart->get($item['id']);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart should have 1 item on it');
        $this->assertEquals(456, $this->cart->getContent()->first()['id'], 'The first content must have ID of 456');
        $this->assertEquals('Sample Item', $this->cart->getContent()->first()['name'], 'The first content must have name of "Sample Item"');
        $this->assertInstanceOf(\stdClass::class, $addedItem->model);
    }

    public function test_cart_can_add_items_with_multidimensional_array_with_associated_model()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 4,
                'attributes' => array(),
                'associatedModel' => new \stdClass()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 4,
                'attributes' => array(),
                'associatedModel' => new \stdClass()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 4,
                'attributes' => array(),
                'associatedModel' => new \stdClass()
            ),
        );

        $this->cart->add($items);

        $content = $this->cart->getContent();
        foreach ($content as $item) {
            $this->assertInstanceOf(\stdClass::class, $item->model);
        }

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertCount(3, $this->cart->getContent()->toArray(), 'Cart should have 3 items');
        $this->assertIsInt($this->cart->getTotalQuantity(), 'Return type should be INT');
        $this->assertEquals(12, $this->cart->getTotalQuantity(),  'Cart\'s quantity should be 4.');
    }
}
