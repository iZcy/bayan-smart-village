// resources/js/Components/Cards/ImageCard.jsx
export const ImageCard = ({
    imageUrl,
    title,
    subtitle,
    badges = [],
    overlayContent,
    aspectRatio = "aspect-video",
    placeholderIcon = "📷",
    placeholderGradient = "from-blue-500 to-purple-600",
}) => {
    return (
        <div className={`relative ${aspectRatio} overflow-hidden`}>
            {imageUrl ? (
                <img
                    src={imageUrl}
                    alt={title}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />
            ) : (
                <div
                    className={`w-full h-full bg-gradient-to-br ${placeholderGradient} flex items-center justify-center`}
                >
                    <span className="text-4xl text-white">
                        {placeholderIcon}
                    </span>
                </div>
            )}

            {/* Overlay */}
            <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-300" />

            {/* Badges */}
            {badges.map((badge, index) => (
                <div
                    key={index}
                    className={`absolute ${badge.position || "top-4 left-4"} ${
                        badge.className ||
                        "bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium"
                    }`}
                >
                    {badge.content}
                </div>
            ))}

            {/* Overlay Content */}
            {overlayContent && (
                <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                    {overlayContent}
                </div>
            )}
        </div>
    );
};

// resources/js/Components/Cards/PlaceCard.jsx
export const PlaceCard = ({ place, index, village }) => {
    const getTypeColor = () => {
        return place.category?.type === "tourism"
            ? "from-green-500 to-teal-600"
            : "from-blue-500 to-purple-600";
    };

    const getTypeIcon = () => {
        return place.category?.type === "tourism" ? "🏞️" : "🏪";
    };

    const badges = [
        {
            content:
                place.category?.type === "tourism"
                    ? "🏞️ Tourism"
                    : "🏪 Business",
            className: `px-3 py-1 rounded-full text-xs font-medium text-white ${
                place.category?.type === "tourism"
                    ? "bg-green-500"
                    : "bg-blue-500"
            }`,
            position: "top-4 left-4",
        },
    ];

    if (place.address) {
        badges.push({
            content: `📍 ${place.address.substring(0, 20)}...`,
            className:
                "bg-black/50 backdrop-blur-sm text-white px-2 py-1 rounded-full text-xs",
            position: "bottom-4 right-4",
        });
    }

    return (
        <BaseCard
            href={`/places/${place.slug}`}
            index={index}
            className="overflow-hidden"
        >
            <ImageCard
                imageUrl={place.image_url}
                title={place.name}
                badges={badges}
                placeholderIcon={getTypeIcon()}
                placeholderGradient={getTypeColor()}
                aspectRatio="h-56"
            />

            <div className="p-6">
                <div className="flex items-center justify-between mb-3">
                    {place.category && (
                        <span
                            className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${
                                place.category.type === "tourism"
                                    ? "bg-green-500/20 text-green-300 border-green-500/30"
                                    : "bg-blue-500/20 text-blue-300 border-blue-500/30"
                            }`}
                        >
                            {place.category.name}
                        </span>
                    )}
                    {place.phone_number && (
                        <span className="text-xs text-gray-400">
                            📞 Contact
                        </span>
                    )}
                </div>

                <h3 className="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors duration-300 line-clamp-2">
                    {place.name}
                </h3>

                <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed text-sm">
                    {place.description?.substring(0, 150)}
                    {place.description?.length > 150 && "..."}
                </p>

                <div className="flex items-center justify-between">
                    <div className="flex items-center text-sm text-gray-400">
                        {place.latitude && place.longitude ? (
                            <span className="flex items-center">
                                <svg
                                    className="w-4 h-4 mr-1"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                Located
                            </span>
                        ) : (
                            <span>📍 Address Available</span>
                        )}
                    </div>
                    <span className="group-hover:text-cyan-300 transition-colors duration-300 text-sm">
                        View Details →
                    </span>
                </div>

                {/* Custom Fields Preview */}
                {place.custom_fields &&
                    Object.keys(place.custom_fields).length > 0 && (
                        <div className="mt-3 flex flex-wrap gap-1">
                            {Object.entries(place.custom_fields)
                                .slice(0, 2)
                                .map(([key, value]) => (
                                    <span
                                        key={key}
                                        className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded"
                                    >
                                        {key}: {value}
                                    </span>
                                ))}
                            {Object.keys(place.custom_fields).length > 2 && (
                                <span className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded">
                                    +
                                    {Object.keys(place.custom_fields).length -
                                        2}{" "}
                                    more
                                </span>
                            )}
                        </div>
                    )}
            </div>
        </BaseCard>
    );
};

// resources/js/Components/Cards/ProductCard.jsx
export const ProductCard = ({ product, index, village }) => {
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
            content: product.availability?.replace("_", " ").toUpperCase(),
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

                <h3 className="text-xl font-bold text-white mb-2 group-hover:text-green-300 transition-colors duration-300 line-clamp-2">
                    {product.name}
                </h3>

                <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed text-sm">
                    {product.short_description ||
                        product.description
                            ?.replace(/<[^>]*>/g, "")
                            .substring(0, 120)}
                    {(product.short_description?.length > 120 ||
                        product.description?.length > 120) &&
                        "..."}
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
                    <span className="group-hover:text-green-300 transition-colors duration-300 text-sm">
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
