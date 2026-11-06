<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXES = [
        'products' => [
            'products_category_listing_index' => ['primary_category_id', 'review_status', 'sort'],
            'products_listing_index' => ['review_status', 'sort'],
            'products_featured_index' => ['is_featured', 'review_status', 'sort'],
        ],
        'media' => [
            'media_model_collection_index' => ['model_type', 'model_id', 'collection_name', 'order_column'],
        ],
        'quote_requests' => [
            'quote_requests_status_created_index' => ['status', 'created_at'],
            'quote_requests_assignee_status_index' => ['assigned_to', 'status'],
            'quote_requests_created_index' => ['created_at'],
        ],
        'quote_request_items' => [
            'quote_request_items_order_index' => ['quote_request_id', 'sort'],
        ],
        'product_variants' => [
            'product_variants_order_index' => ['product_id', 'sort'],
        ],
        'categories' => [
            'categories_listing_index' => ['review_status', 'sort'],
        ],
        'services' => [
            'services_listing_index' => ['review_status', 'sort'],
        ],
        'industries' => [
            'industries_listing_index' => ['review_status', 'sort'],
        ],
        'work_items' => [
            'work_items_listing_index' => ['review_status', 'sort'],
        ],
        'guides' => [
            'guides_listing_index' => ['review_status', 'published_at'],
        ],
        'faqs' => [
            'faqs_listing_index' => ['review_status', 'show_on_home', 'sort'],
        ],
        'search_logs' => [
            'search_logs_period_query_index' => ['created_at', 'query'],
        ],
        'activity_log' => [
            'activity_log_event_index' => ['event'],
        ],
    ];

    private const REDUNDANT = [
        'products' => ['products_review_status_index' => ['review_status'], 'products_is_featured_index' => ['is_featured']],
        'media' => ['media_model_type_model_id_index' => ['model_type', 'model_id']],
        'quote_requests' => ['quote_requests_status_index' => ['status']],
        'categories' => ['categories_review_status_index' => ['review_status']],
        'services' => ['services_review_status_index' => ['review_status']],
        'industries' => ['industries_review_status_index' => ['review_status']],
        'work_items' => ['work_items_review_status_index' => ['review_status']],
        'guides' => ['guides_review_status_index' => ['review_status']],
        'faqs' => ['faqs_review_status_index' => ['review_status']],
        'search_logs' => ['search_logs_created_at_index' => ['created_at']],
    ];

    private const FOREIGN_KEY_INDEXES = [
        'products' => ['products_primary_category_id_foreign' => ['primary_category_id']],
        'product_variants' => ['product_variants_product_id_foreign' => ['product_id']],
        'quote_request_items' => ['quote_request_items_quote_request_id_foreign' => ['quote_request_id']],
        'quote_requests' => ['quote_requests_assigned_to_foreign' => ['assigned_to']],
    ];

    public function up(): void
    {
        $this->addIndexes(self::INDEXES);
        $this->dropIndexes(self::REDUNDANT);
        $this->dropIndexes(self::FOREIGN_KEY_INDEXES);
    }

    public function down(): void
    {
        $this->addIndexes(self::FOREIGN_KEY_INDEXES);
        $this->addIndexes(self::REDUNDANT);
        $this->dropIndexes(self::INDEXES);
    }

    private function addIndexes(array $definitions): void
    {
        foreach ($definitions as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                }
            }
        }
    }

    private function dropIndexes(array $definitions): void
    {
        foreach ($definitions as $table => $indexes) {
            foreach (array_map('strval', array_keys($indexes)) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }
};
