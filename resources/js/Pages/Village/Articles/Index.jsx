// resources/js/Pages/Articles/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion, AnimatePresence, useScroll, useTransform } from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import MediaBackground from "@/Components/MediaBackground";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { ArticleCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";

const ArticlesPage = ({ village, articles, filters = {} }) => {
    // Ensure we have valid data
    const articleData = articles?.data || [];
    const filterData = filters || {};

    const [filteredArticles, setFilteredArticles] = useState(articleData);
    const [searchTerm, setSearchTerm] = useState(filterData.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filterData.category || ""
    );
    const [sortBy, setSortBy] = useState(filterData.sort || "newest");
    const [currentSlide, setCurrentSlide] = useState(0);
    const { scrollY } = useScroll();

    // Color overlay for Articles sections - multiple scroll points for footer visibility
    const colorOverlay = useTransform(
        scrollY,
        [0, 800, 1600, 2400],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.5))", // Hero - darker for better card visibility
            "linear-gradient(to bottom, rgba(126,34,206,0.7), rgba(88,28,135,0.8))", // Articles Grid - purple, darker for better card visibility
            "linear-gradient(to bottom, rgba(88,28,135,0.6), rgba(55,48,163,0.7))", // Mid transition
            "linear-gradient(to bottom, rgba(55,48,163,0.4), rgba(0,0,0,0.6))", // End fade to black for footer
        ]
    );

    // Get featured article images for slideshow
    const featuredImages = articleData.slice(0, 5).map((article) => ({
        id: article.id,
        image_url: article.cover_image_url,
        title: article.title,
        subtitle: article.published_at
            ? new Date(article.published_at).toLocaleDateString()
            : "Village story",
    }));

    useEffect(() => {
        let filtered = articleData;

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(
                (article) =>
                    article.title
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    article.content
                        ?.toLowerCase()
                        .includes(searchTerm.toLowerCase())
            );
        }

        // Filter by category (could be place, community, etc.)
        if (selectedCategory) {
            filtered = filtered.filter((article) => {
                return (
                    article.place?.id === selectedCategory ||
                    article.community?.id === selectedCategory ||
                    article.sme?.id === selectedCategory
                );
            });
        }

        // Sort articles
        switch (sortBy) {
            case "title":
                filtered.sort((a, b) => a.title.localeCompare(b.title));
                break;
            case "oldest":
                filtered.sort(
                    (a, b) =>
                        new Date(a.published_at || a.created_at) -
                        new Date(b.published_at || b.created_at)
                );
                break;
            case "featured":
                filtered.sort((a, b) => {
                    if (a.is_featured && !b.is_featured) return -1;
                    if (!a.is_featured && b.is_featured) return 1;
                    return (
                        new Date(b.published_at || b.created_at) -
                        new Date(a.published_at || a.created_at)
                    );
                });
                break;
            default: // newest
                filtered.sort(
                    (a, b) =>
                        new Date(b.published_at || b.created_at) -
                        new Date(a.published_at || a.created_at)
                );
        }

        setFilteredArticles(filtered);
    }, [searchTerm, selectedCategory, sortBy, articleData]);

    // Extract unique categories from articles
    const categories = [
        ...new Map(
            articleData
                .flatMap((article) => [
                    article.place && {
                        id: article.place.id,
                        name: `📍 ${article.place.name}`,
                        type: "place",
                    },
                    article.community && {
                        id: article.community.id,
                        name: `👥 ${article.community.name}`,
                        type: "community",
                    },
                    article.sme && {
                        id: article.sme.id,
                        name: `🏪 ${article.sme.name}`,
                        type: "product",
                    },
                ])
                .filter(Boolean)
                .map((item) => [item.id, item])
        ).values(),
    ];

    // Sort options for articles
    const sortOptions = [
        { value: "newest", label: "Newest First" },
        { value: "oldest", label: "Oldest First" },
        { value: "featured", label: "Featured" },
        { value: "title", label: "Title A-Z" },
    ];

    const handleClearFilters = () => {
        setSearchTerm("");
        setSelectedCategory("");
        setSortBy("newest");
    };

    // Sort filter component
    const sortFilterComponent = (
        <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value)}
            className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
        >
            {sortOptions.map((option) => (
                <option
                    key={option.value}
                    value={option.value}
                    className="text-black"
                >
                    {option.label}
                </option>
            ))}
        </select>
    );

    // Auto-advance slideshow
    useEffect(() => {
        if (featuredImages.length > 1) {
            const interval = setInterval(() => {
                setCurrentSlide((prev) => (prev + 1) % featuredImages.length);
            }, 6000);
            return () => clearInterval(interval);
        }
    }, [featuredImages.length]);

    return (
        <MainLayout title="Articles">
            <Head title={`Articles - ${village?.name}`} />

            {/* Media Background with blur for content sections */}
            <MediaBackground
                context="articles"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                controlsId="articles-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Enhanced Color Overlay */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{ background: colorOverlay }}
            />

            {/* Fixed Hero Background */}
            <div className="fixed inset-0 z-0">
                {/* Slideshow Background */}
                {featuredImages.length > 0 && (
                    <div className="absolute inset-0">
                        <AnimatePresence>
                            <motion.div
                                key={currentSlide}
                                initial={{ opacity: 0, scale: 1.1 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 1.1 }}
                                transition={{ duration: 1.5, ease: "easeInOut" }}
                                className="absolute inset-0"
                            >
                                {featuredImages[currentSlide]?.image_url && (
                                    <img
                                        src={
                                            featuredImages[currentSlide]
                                                .image_url
                                        }
                                        alt={featuredImages[currentSlide].title}
                                        className="w-full h-full object-cover"
                                    />
                                )}
                            </motion.div>
                        </AnimatePresence>
                        
                        {/* Slideshow indicators */}
                        <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex gap-2 z-30">
                            {featuredImages.map((_, index) => (
                                <button
                                    key={index}
                                    onClick={() => setCurrentSlide(index)}
                                    className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                        index === currentSlide
                                            ? "bg-white scale-125"
                                            : "bg-white/50 hover:bg-white/75"
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden z-10">
                {/* Content overlay for readability */}
                <div className="absolute inset-0 bg-black/40 z-5"></div>

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-20">
                    <div className="max-w-4xl px-6">
                        <motion.h1
                            initial={{ opacity: 0, y: 50 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="text-6xl md:text-8xl font-bold text-white mb-6"
                        >
                            Village Stories
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            Discover the rich history, culture, and daily life
                            of {village?.name}
                        </motion.p>

                        <FilterControls
                            searchTerm={searchTerm}
                            setSearchTerm={setSearchTerm}
                            selectedCategory={selectedCategory}
                            setSelectedCategory={setSelectedCategory}
                            categories={categories}
                            additionalFilters={[
                                { component: sortFilterComponent },
                            ]}
                            searchPlaceholder="Search articles..."
                            className="max-w-4xl mx-auto relative z-25"
                        />
                    </div>
                </div>

                {/* Scroll Indicator */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 2, duration: 1 }}
                    className="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white z-30"
                >
                    <motion.div
                        animate={{ y: [0, 10, 0] }}
                        transition={{ repeat: Infinity, duration: 2 }}
                        className="flex flex-col items-center"
                    >
                        <span className="text-sm mb-2">Scroll to explore</span>
                        <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center">
                            <motion.div
                                animate={{ y: [0, 12, 0] }}
                                transition={{ repeat: Infinity, duration: 2 }}
                                className="w-1 h-3 bg-white/70 rounded-full mt-2"
                            />
                        </div>
                    </motion.div>
                </motion.div>
            </section>

            {/* Articles Grid Section */}
            <section className="min-h-screen relative overflow-hidden py-20 z-10">
                <div className="container mx-auto px-6">
                    <SectionHeader
                        title="Article"
                        count={filteredArticles.length}
                        gradientColor="from-blue-400 to-purple-500"
                    />

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {filteredArticles.map((article, index) => (
                            <ArticleCard
                                key={article.id}
                                article={article}
                                index={index}
                                village={village}
                            />
                        ))}
                    </div>

                    {filteredArticles.length === 0 && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            className="text-center py-20"
                        >
                            <div className="text-6xl mb-4">📖</div>
                            <h3 className="text-2xl font-semibold text-white mb-2">
                                No Articles Found
                            </h3>
                            <p className="text-gray-400">
                                Try adjusting your search terms
                            </p>
                        </motion.div>
                    )}

                    {/* Pagination */}
                    {articles?.last_page > 1 && (
                        <Pagination
                            paginationData={articles}
                            theme="articles"
                        />
                    )}
                </div>
            </section>
        </MainLayout>
    );
};

export default ArticlesPage;
