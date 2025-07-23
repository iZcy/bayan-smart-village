// resources/js/Components/Cards/ProductCard.jsx

import BaseCard from "./BaseCard";
import ImageCard from "./ImageCard";

const ProductCard = ({ product, index, village }) => {
    const getDisplayPrice = () => {
        if (product.price) {
            return `Rp ${new Intl.NumberFormat("id-ID").format(product.price)}`;
        }
        if (product.price_range_min && product.price_range_max) {
            return `Rp ${new Intl.NumberFormat("id-ID").format(
                product.price_range_min
            )} - ${new Intl.NumberFormat("id-ID").format(
                product.price_range_max
            )}`;
        }
        if (product.price_range_min) {
            return `From Rp ${new Intl.NumberFormat("id-ID").format(
                product.price_range_min
            )}`;
        }
        return "Contact for price";
    };

    const getAvailabilityColor = () => {
        switch (product.availability) {
            case "available":
                return "bg-green-500";
            case "out_of_stock":
                return "bg-red-500";
            case "seasonal":
                return "bg-yellow-500";
            case "on_demand":
                return "bg-blue-500";
            default:
                return "bg-gray-500";
        }
    };

    const badges = [
        {
            content: product.availability?.replaceAll("_", " ").toUpperCase(),
            className: `px-2 py-1 rounded-full text-xs font-medium text-white ${getAvailabilityColor()}`,
            position: "top-4 left-4",
        },
    ];

    if (product.is_featured) {
        badges.push({
            content: "⭐ Featured",
            className:
                "bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium",
            position: "top-4 right-4",
        });
    }

    if (product.view_count) {
        badges.push({
            content: `👁️ ${product.view_count}`,
            className:
                "bg-black/50 backdrop-blur-sm text-white px-2 py-1 rounded-full text-xs",
            position: "bottom-4 right-4",
        });
    }

    return (
        <BaseCard href={`/products/${product.slug}`} index={index}>
            <ImageCard
                imageUrl={product.primary_image_url}
                title={product.name}
                badges={badges}
                placeholderIcon="📦"
                placeholderGradient="from-green-500 to-emerald-600"
                aspectRatio="h-56"
            />

            <div className="p-6">
                <div className="flex items-center justify-between mb-2">
                    {product.category && (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                            {product.category.name}
                        </span>
                    )}
                    {product.sme && (
                        <span className="text-xs text-gray-400">
                            🏪 {product.sme.name}
                        </span>
                    )}
                </div>

                <h3 className="text-xl font-bold text-white mb-2 group-hover:text-green-300 transition-colors duration-300 line-clamp-1">
                    {product.name}
                </h3>

                <p className="text-gray-300 mb-4 leading-relaxed text-sm line-clamp-3">
                    {product.short_description ||
                        product.description?.replace(/<[^>]*>/g, "")}
                </p>

                <div className="flex items-center justify-between">
                    <div className="text-green-300 font-bold text-lg">
                        {getDisplayPrice()}
                        {product.price_unit && (
                            <span className="text-xs text-gray-400 ml-1">
                                /{product.price_unit}
                            </span>
                        )}
                    </div>
                    <span className="group-hover:text-green-300 transition-colors duration-300 text-sm text-white">
                        View Details →
                    </span>
                </div>

                {/* E-commerce links count */}
                {product.ecommerce_links_count > 0 && (
                    <div className="mt-3 text-xs text-gray-400">
                        🛒 Available on {product.ecommerce_links_count} platform
                        {product.ecommerce_links_count !== 1 ? "s" : ""}
                    </div>
                )}

                {/* Tags */}
                {product.tags && product.tags.length > 0 && (
                    <div className="mt-3 flex flex-wrap gap-1">
                        {product.tags.slice(0, 3).map((tag) => (
                            <span
                                key={tag.id}
                                className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded"
                            >
                                #{tag.name}
                            </span>
                        ))}
                        {product.tags.length > 3 && (
                            <span className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded">
                                +{product.tags.length - 3}
                            </span>
                        )}
                    </div>
                )}
            </div>
        </BaseCard>
    );
};

export default ProductCard;
