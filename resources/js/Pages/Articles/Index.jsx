// resources/js/Pages/Village/Articles/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { ArticleCard } from "@/Components/Cards/BaseCard";
import Pagination from "@/Components/Pagination";

const ArticlesPage = ({ village, articles, filters }) => {
    const [filteredArticles, setFilteredArticles] = useState(articles.data);
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filters.category || ""
    );
    const [sortBy, setSortBy] = useState(filters.sort || "newest");

    useEffect(() => {
        let filtered = articles.data;

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
    }, [searchTerm, selectedCategory, sortBy, articles.data]);

    // Extract unique categories from articles
    const categories = [
        ...new Map(
            articles.data
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
                        type: "sme",
                    },
                ])
                .filter(Boolean)
                .map((item) => [item.id, item])
        ).values(),
    ];

    return (
        <MainLayout title="Articles">
            <Head title={`Articles - ${village?.name}`} />

            {/* Hero Section */}
            <HeroSection
                title="Village Stories"
                subtitle={`Discover the rich history, culture, and daily life of ${village?.name}`}
                backgroundGradient="from-slate-600 via-blue-500 to-purple-700"
                parallax={true}
                scrollY={{ useTransform: (scrollY) => scrollY }}
            >
                <FilterControls
                    searchTerm={searchTerm}
                    setSearchTerm={setSearchTerm}
                    selectedCategory={selectedCategory}
                    setSelectedCategory={setSelectedCategory}
                    categories={categories}
                    sortBy={sortBy}
                    setSortBy={setSortBy}
                    searchPlaceholder="Search articles..."
                />
            </HeroSection>

            {/* Articles Grid Section */}
            <section className="py-20 bg-gradient-to-b from-purple-700 to-slate-900">
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
                    {articles.last_page > 1 && (
                        <Pagination
                            paginationData={articles}
                            theme="articles"
                        />
                    )}
                </div>
            </section>

            {/* Article Categories Section */}
            {categories.length > 0 && (
                <section className="py-20 bg-gradient-to-b from-slate-900 to-blue-900">
                    <div className="container mx-auto px-6">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-4xl font-bold text-white text-center mb-12"
                        >
                            Browse by Source
                        </motion.h2>

                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            {categories.slice(0, 8).map((category, index) => (
                                <motion.button
                                    key={category.id}
                                    initial={{ opacity: 0, y: 30 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    transition={{
                                        duration: 0.6,
                                        delay: index * 0.1,
                                    }}
                                    whileHover={{ scale: 1.05, y: -5 }}
                                    whileTap={{ scale: 0.95 }}
                                    onClick={() =>
                                        setSelectedCategory(category.id)
                                    }
                                    className={`p-6 rounded-xl backdrop-blur-md border transition-all duration-300 ${
                                        selectedCategory === category.id
                                            ? "bg-white/20 border-white/40"
                                            : "bg-white/10 border-white/20 hover:bg-white/15"
                                    }`}
                                >
                                    <div className="text-2xl mb-3">
                                        {category.type === "place"
                                            ? "📍"
                                            : category.type === "community"
                                            ? "👥"
                                            : "🏪"}
                                    </div>
                                    <h3 className="text-white font-semibold text-sm">
                                        {category.name}
                                    </h3>
                                    <p className="text-gray-300 text-xs mt-1">
                                        {
                                            articles.data.filter((article) =>
                                                category.type === "place"
                                                    ? article.place?.id ===
                                                      category.id
                                                    : category.type ===
                                                      "community"
                                                    ? article.community?.id ===
                                                      category.id
                                                    : article.sme?.id ===
                                                      category.id
                                            ).length
                                        }{" "}
                                        articles
                                    </p>
                                </motion.button>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Featured Articles Section */}
            <section className="py-20 bg-gradient-to-b from-blue-900 to-purple-900">
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-4xl font-bold text-white text-center mb-12"
                    >
                        Featured Stories
                    </motion.h2>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {[
                            {
                                title: "Village History",
                                description:
                                    "Stories about the rich history and heritage",
                                icon: "🏛️",
                                count: articles.data.filter(
                                    (a) => a.is_featured
                                ).length,
                                color: "from-yellow-400 to-orange-500",
                            },
                            {
                                title: "Community Life",
                                description:
                                    "Daily life and community activities",
                                icon: "👥",
                                count: articles.data.filter((a) => a.community)
                                    .length,
                                color: "from-green-400 to-emerald-500",
                            },
                            {
                                title: "Local Places",
                                description:
                                    "Stories about special places and landmarks",
                                icon: "📍",
                                count: articles.data.filter((a) => a.place)
                                    .length,
                                color: "from-blue-400 to-cyan-500",
                            },
                        ].map((type, index) => (
                            <motion.div
                                key={type.title}
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{
                                    duration: 0.6,
                                    delay: index * 0.2,
                                }}
                                whileHover={{ scale: 1.05, y: -10 }}
                                className="bg-white/10 backdrop-blur-md rounded-xl p-8 border border-white/20 text-center"
                            >
                                <motion.div
                                    className={`w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r ${type.color} flex items-center justify-center text-2xl`}
                                    animate={{ rotate: [0, 5, -5, 0] }}
                                    transition={{
                                        duration: 2,
                                        repeat: Infinity,
                                        delay: index,
                                    }}
                                >
                                    {type.icon}
                                </motion.div>
                                <h3 className="text-xl font-bold text-white mb-2">
                                    {type.title}
                                </h3>
                                <p className="text-gray-300 text-sm mb-4">
                                    {type.description}
                                </p>
                                <div className="text-3xl font-bold text-white">
                                    {type.count}
                                </div>
                                <div className="text-gray-400 text-sm">
                                    articles
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Stats Section */}
            <section className="py-20 bg-gradient-to-b from-purple-900 to-slate-800">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="grid grid-cols-2 md:grid-cols-4 gap-8"
                    >
                        {[
                            {
                                label: "Total Articles",
                                value: articles.total || 0,
                                icon: "📖",
                            },
                            {
                                label: "Featured",
                                value:
                                    articles.data?.filter((a) => a.is_featured)
                                        .length || 0,
                                icon: "⭐",
                            },
                            {
                                label: "Authors",
                                value:
                                    new Set(
                                        articles.data
                                            ?.map((a) => a.author_id)
                                            .filter(Boolean)
                                    ).size || 1,
                                icon: "✍️",
                            },
                            {
                                label: "This Year",
                                value:
                                    articles.data?.filter((a) =>
                                        new Date(a.published_at || a.created_at)
                                            .getFullYear()
                                            .toString()
                                            .includes(
                                                new Date()
                                                    .getFullYear()
                                                    .toString()
                                            )
                                    ).length || 0,
                                icon: "📅",
                            },
                        ].map((stat, index) => (
                            <motion.div
                                key={stat.label}
                                initial={{ scale: 0, rotateY: 90 }}
                                whileInView={{ scale: 1, rotateY: 0 }}
                                transition={{
                                    delay: index * 0.2,
                                    duration: 0.6,
                                    type: "spring",
                                }}
                                whileHover={{ scale: 1.1, y: -5 }}
                                className="text-center text-white bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/20"
                            >
                                <motion.div
                                    animate={{ rotate: [0, 10, -10, 0] }}
                                    transition={{
                                        duration: 2,
                                        repeat: Infinity,
                                        delay: index,
                                    }}
                                    className="text-3xl mb-2"
                                >
                                    {stat.icon}
                                </motion.div>
                                <div className="text-4xl font-bold mb-2">
                                    {stat.value}
                                </div>
                                <div className="text-gray-300">
                                    {stat.label}
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>
                </div>
            </section>
        </MainLayout>
    );
};

export default ArticlesPage;
