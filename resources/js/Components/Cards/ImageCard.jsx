// resources/js/Components/Cards/ImageCard.jsx
const ImageCard = ({
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

export default ImageCard;
