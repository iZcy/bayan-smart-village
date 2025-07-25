// resources/js/Pages/Articles/Index.jsx
import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import {
    motion,
    AnimatePresence,
    useScroll,
    useTransform,
} from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import MediaBackground from "@/Components/MediaBackground";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { ArticleCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";
import SlideshowBackground from "@/Components/SlideshowBackground";
import { useSlideshowData, slideshowConfigs } from "@/hooks/useSlideshowData";

const ArticlesPage = ({ village, articles, filters = {} }) => {
    // Ensure we have valid data
    const articleData = articles?.data || [];
    const filterData = filters || {};

    const [filteredArticles, setFilteredArticles] = useState(articleData);
    const [searchTerm, setSearchTerm] = useState(filterData.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filterData.category || ""
    );
    // New separate filter states
    const [selectedPlace, setSelectedPlace] = useState(filterData.place || "");
    const [selectedCommunity, setSelectedCommunity] = useState(filterData.community || "");
    const [selectedSme, setSelectedSme] = useState(filterData.sme || "");
    const [sortBy, setSortBy] = useState(filterData.sort || "newest");
    const { scrollY } = useScroll();

    // Prepare slideshow data using the custom hook
    const slideshowImages = useSlideshowData(
        articleData,
        slideshowConfigs.articles
    );

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

        // Filter by category (legacy support - could be place, community, etc.)
        if (selectedCategory) {
            filtered = filtered.filter((article) => {
                return (
                    article.place?.id === selectedCategory ||
                    article.community?.id === selectedCategory ||
                    article.sme?.id === selectedCategory
                );
            });
        }

        // Filter by specific place
        if (selectedPlace) {
            filtered = filtered.filter((article) => 
                article.place?.id === selectedPlace
            );
        }

        // Filter by specific community
        if (selectedCommunity) {
            filtered = filtered.filter((article) => 
                article.community?.id === selectedCommunity
            );
        }

        // Filter by specific SME
        if (selectedSme) {
            filtered = filtered.filter((article) => 
                article.sme?.id === selectedSme
            );
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
    }, [searchTerm, selectedCategory, selectedPlace, selectedCommunity, selectedSme, sortBy, articleData]);

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

    // Extract separate arrays for each filter type
    const places = [
        ...new Map(
            articleData
                .filter(article => article.place)
                .map(article => [article.place.id, article.place])
        ).values(),
    ];

    const communities = [
        ...new Map(
            articleData
                .filter(article => article.community)
                .map(article => [article.community.id, article.community])
        ).values(),
    ];

    const smes = [
        ...new Map(
            articleData
                .filter(article => article.sme)
                .map(article => [article.sme.id, article.sme])
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

            {/* Slideshow Background */}
            <SlideshowBackground
                images={slideshowImages}
                interval={slideshowConfigs.articles.interval}
                transitionDuration={
                    slideshowConfigs.articles.transitionDuration
                }
                placeholderConfig={slideshowConfigs.articles.placeholderConfig}
            />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden z-10">
                {/* Content overlay for readability */}
                <div className="absolute inset-0 bg-black/40 z-5"></div>

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-20 flex-col gap-4">
                    <div className="max-w-4xl px-6">
                        {/* Breadcrumb */}
                        <motion.nav
                            className="mb-8"
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                        >
                            <div className="flex items-center justify-center space-x-2 text-white/70">
                                <Link
                                    href="/"
                                    className="hover:text-white transition-colors"
                                >
                                    {village.name}
                                </Link>
                                <span>/</span>
                                <span className="text-white">Articles</span>
                            </div>
                        </motion.nav>

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
                            // Legacy category support
                            selectedCategory={selectedCategory}
                            setSelectedCategory={setSelectedCategory}
                            categories={categories}
                            // New separate filters
                            selectedPlace={selectedPlace}
                            setSelectedPlace={setSelectedPlace}
                            places={places}
                            selectedCommunity={selectedCommunity}
                            setSelectedCommunity={setSelectedCommunity}
                            communities={communities}
                            selectedSme={selectedSme}
                            setSelectedSme={setSelectedSme}
                            smes={smes}
                            additionalFilters={[
                                { component: sortFilterComponent },
                            ]}
                            searchPlaceholder="Search articles..."
                            className="max-w-4xl mx-auto relative z-25"
                        />
                    </div>

                    {/* Scroll Indicator */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 2, duration: 1 }}
                        className="transform text-white z-30"
                    >
                        <motion.div
                            animate={{ y: [0, 10, 0] }}
                            transition={{ repeat: Infinity, duration: 2 }}
                            className="flex flex-col items-center"
                        >
                            <span className="text-sm mb-2">
                                Scroll to explore
                            </span>
                            <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center">
                                <motion.div
                                    animate={{ y: [0, 12, 0] }}
                                    transition={{
                                        repeat: Infinity,
                                        duration: 2,
                                    }}
                                    className="w-1 h-3 bg-white/70 rounded-full mt-2"
                                />
                            </div>
                        </motion.div>
                    </motion.div>
                </div>
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
