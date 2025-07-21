// resources/js/Pages/Articles/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
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

    return (
        <MainLayout title="Articles">
            <Head title={`Articles - ${village?.name}`} />

            {/* Hero Section with Integrated Filters */}
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
                    additionalFilters={[{ component: sortFilterComponent }]}
                    searchPlaceholder="Search articles..."
                    className="max-w-4xl mx-auto"
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
