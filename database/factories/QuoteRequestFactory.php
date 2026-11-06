<?php

namespace Database\Factories;

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    public function definition(): array
    {
        return [
            'idempotency_key' => (string) Str::uuid(),
            'name' => fake()->name(),
            'phone' => '07'.fake()->numberBetween(70000000, 99999999),
            'company' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'city' => fake()->randomElement(['عمّان', 'الزرقاء', 'إربد', 'السلط']),
            'fulfillment' => fake()->randomElement(FulfillmentMethod::cases()),
            'needed_by' => fake()->optional()->dateTimeBetween('+1 week', '+2 months'),
            'notes' => fake()->optional()->sentence(),
            'privacy_accepted_at' => now(),
            'locale' => fake()->randomElement(['ar', 'en']),
            'source' => ['landing_path' => '/ar/products', 'utm_source' => fake()->optional()->randomElement(['google', 'facebook'])],
        ];
    }

    public function status(QuoteStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (Model $quote) use ($count) {
            if (! $quote instanceof QuoteRequest) {
                return;
            }

            $products = Product::inRandomOrder()->limit($count)->get();

            foreach ($products as $index => $product) {
                $quote->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->getTranslation('name', $quote->locale),
                    'product_url' => "/{$quote->locale}/products/{$product->slug}",
                    'thickness' => fake()->randomElement(['1.5 mm', '2 mm', '3 mm', null]),
                    'quantity' => fake()->numberBetween(1, 50),
                    'unit' => fake()->randomElement(['piece', 'sheet', 'ton']),
                    'sort' => $index,
                ]);
            }

            $quote->items()->create([
                'product_name' => 'صاج مثقب 2 ملم',
                'is_custom' => true,
                'quantity' => 10,
                'unit' => 'sheet',
                'sort' => $count,
            ]);
        });
    }
}
