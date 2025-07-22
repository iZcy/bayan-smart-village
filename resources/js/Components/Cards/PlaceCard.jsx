// resources/js/Components/Cards/PlaceCard.jsx

import BaseCard from "./BaseCard";
import ImageCard from "./ImageCard";

const PlaceCard = ({ place, index, village }) => {
    const getTypeColor = () => {
        return place.category?.type === "service"
            ? "from-green-500 to-teal-600"
            : "from-blue-500 to-purple-600";
    };

    const getTypeIcon = () => {
        return place.category?.type === "service" ? "🏞️" : "🏪";
    };

    const badges = [
        {
            content:
                place.category?.type === "service"
                    ? "🏞️ Tourism"
                    : "🏪 Business",
            className: `px-3 py-1 rounded-full text-xs font-medium text-white ${
                place.category?.type === "service"
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
                                place.category.type === "service"
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

                <h3 className="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors duration-300 line-clamp-1">
                    {place.name}
                </h3>

                <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed text-sm h-[68px] overflow-hidden">
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
                    <span className="group-hover:text-cyan-300 transition-colors duration-300 text-sm text-white">
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

export default PlaceCard;
