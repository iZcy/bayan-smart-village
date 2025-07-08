<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductEcommerceLink;
use App\Models\Village;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * Get popular products across all villages
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        return Product::with(['village', 'category', 'ecommerceLinks'])
            ->active()
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending products (high recent views)
     */
    public function getTrendingProducts(int $limit = 8): Collection
    {
        return Product::with(['village', 'category', 'ecommerceLinks'])
            ->active()
            ->where('created_at', '>=', now()->subMonths(3))
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products by village with stats
     */
    public function getVillageProductStats(Village $village): array
    {
        $products = $village->activeProducts();

        return [
            'total_products' => $products->count(),
            'featured_products' => $products->featured()->count(),
            'categories' => $products->with('category')
                ->get()
                ->groupBy('category.name')
                ->map->count()
                ->toArray(),
            'total_views' => $products->sum('view_count'),
            'products_with_ecommerce' => $products->whereHas('ecommerceLinks')->count(),
        ];
    }

    /**
     * Track platform performance
     */
    public function getPlatformStats(): Collection
    {
        return ProductEcommerceLink::active()
            ->selectRaw('platform, COUNT(*) as total_links, SUM(click_count) as total_clicks, AVG(click_count) as avg_clicks')
            ->groupBy('platform')
            ->orderBy('total_clicks', 'desc')
            ->get();
    }

    /**
     * Get recommended products based on a product
     */
    public function getRecommendedProducts(Product $product, int $limit = 6): Collection
    {
        // Get products from same category, village, or with similar tags
        return Product::with(['village', 'category', 'ecommerceLinks'])
            ->active()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                    ->orWhere('village_id', $product->village_id)
                    ->orWhereHas('tags', function ($q) use ($product) {
                        $q->whereIn('id', $product->tags->pluck('id'));
                    });
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
