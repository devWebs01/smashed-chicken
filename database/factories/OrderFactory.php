<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'status' => $this->faker->randomElement([
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_DRAFT,
                Order::STATUS_CONFIRM
            ]),
            'order_date_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'qris', 'transfer']),
            'total_price' => 0, // Will be calculated after items are added
            'delivery_method' => $this->faker->randomElement(['dine_in', 'takeaway', 'delivery']),
            'device_id' => null,
        ];
    }

    /**
     * Configure the factory to create order with items.
     *
     * @return $this
     */
    public function withItems(int $count = 3): static
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            // Get or create products for the order items
            $products = Product::inRandomOrder()->take($count)->get();

            // If not enough products, create new ones
            if ($products->count() < $count) {
                $needed = $count - $products->count();
                $additionalProducts = Product::factory()->count($needed)->create();
                $products = $products->concat($additionalProducts);
            }

            $totalPrice = 0;

            // Create order items
            foreach ($products as $product) {
                $quantity = $this->faker->numberBetween(1, 5);
                $price = $product->price;
                $subtotal = $price * $quantity;
                $totalPrice += $subtotal;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            // Update the order total price
            $order->update(['total_price' => $totalPrice]);
        });
    }

    /**
     * Indicate that the order is completed.
     *
     * @return static
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate that the order is pending.
     *
     * @return static
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the order is processing.
     *
     * @return static
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     *
     * @return static
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the order is for delivery.
     *
     * @return static
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_method' => 'delivery',
            'customer_address' => $this->faker->address(),
        ]);
    }

    /**
     * Indicate that the order is for dine-in.
     *
     * @return static
     */
    public function dineIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_method' => 'dine_in',
        ]);
    }

    /**
     * Indicate that the order is for takeaway.
     *
     * @return static
     */
    public function takeaway(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_method' => 'takeaway',
        ]);
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get or create a product
        $product = Product::inRandomOrder()->first() ?: Product::factory()->create();

        $quantity = $this->faker->numberBetween(1, 5);
        $price = $product->price;
        $subtotal = $price * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ];
    }
}