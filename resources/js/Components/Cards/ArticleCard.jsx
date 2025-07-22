// resources/js/Components/Cards/ArticleCard.jsx
import BaseCard from "./BaseCard";
import ImageCard from "./ImageCard";

const ArticleCard = ({ article, index, village }) => {
    const getReadingTime = () => {
        const content = article.content?.replace(/<[^>]*>/g, "") || "";
        return Math.ceil(content.split(" ").length / 200) || 5;
    };

    const badges = [
        {
            content: `${getReadingTime()} min read`,
            className:
                "bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm",
            position: "top-4 right-4",
        },
    ];

    if (article.place) {
        badges.push({
            content: `📍 ${article.place.name}`,
            className:
                "bg-blue-500/20 text-blue-300 border border-blue-500/30 px-2.5 py-0.5 rounded-full text-xs font-medium",
            position: "top-4 left-4",
        });
    }

    return (
        <BaseCard href={`/articles/${article.slug}`} index={index}>
            <ImageCard
                imageUrl={article.cover_image_url}
                title={article.title}
                badges={badges}
                placeholderIcon="📰"
                placeholderGradient="from-slate-500 to-gray-600"
                aspectRatio="h-48"
            />

            <div className="p-6">
                <h3 className="text-xl font-bold text-white mb-3 group-hover:text-blue-300 transition-colors duration-300 line-clamp-1">
                    {article.title}
                </h3>

                <p className="text-white/70 text-sm line-clamp-3 mb-4 leading-relaxed h-[68px] overflow-hidden">
                    {article.content?.replace(/<[^>]*>/g, "").substring(0, 150)}
                    ...
                </p>

                <div className="flex items-center justify-between text-sm text-gray-400">
                    <time dateTime={article.created_at}>
                        {new Date(article.created_at).toLocaleDateString(
                            "en-US",
                            {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                            }
                        )}
                    </time>
                    <span className="group-hover:text-blue-300 transition-colors duration-300">
                        Read more →
                    </span>
                </div>
            </div>
        </BaseCard>
    );
};

export default ArticleCard;
