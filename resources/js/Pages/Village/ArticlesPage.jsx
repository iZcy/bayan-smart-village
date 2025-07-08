import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const ArticlesPage = ({ village, articles, filters }) => {
    const [filteredArticles, setFilteredArticles] = useState(articles.data);
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const { scrollY } = useScroll();

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 500], [0, -150]);
    const overlayOpacity = useTransform(scrollY, [0, 300], [0.3, 0.7]);

    useEffect(() => {
        const filtered = articles.data.filter(
            (article) =>
                article.title
                    .toLowerCase()
                    .includes(searchTerm.toLowerCase()) ||
                article.content.toLowerCase().includes(searchTerm.toLowerCase())
        );
        setFilteredArticles(filtered);
    }, [searchTerm, articles.data]);

    return (
        <MainLayout title="Articles">
            <Head title={`Articles - ${village?.name}`} />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden">
                {/* Background with parallax */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-700"
                >
                    <div className="absolute inset-0 opacity-20">
                        <svg viewBox="0 0 1200 600" className="w-full h-full">
                            <path
                                d="M0,600 L0,300 Q200,250 400,280 T800,220 Q1000,200 1200,240 L1200,600 Z"
                                fill="#1e293b"
                            />
                            <path
                                d="M0,600 L0,350 Q300,300 600,320 T1200,300 L1200,600 Z"
                                fill="#334155"
                            />
                        </svg>
                    </div>
                </motion.div>

                {/* Animated overlay */}
                <motion.div
                    style={{ opacity: overlayOpacity }}
                    className="absolute inset-0 bg-black"
                />

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
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

                        {/* Search Bar */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.5 }}
                            className="max-w-md mx-auto"
                        >
                            <div className="relative">
                                <input
                                    type="text"
                                    placeholder="Search articles..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full px-6 py-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50"
                                />
                                <div className="absolute right-4 top-1/2 transform -translate-y-1/2">
                                    <svg
                                        className="w-5 h-5 text-gray-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Articles Grid Section */}
            <section className="py-20 bg-gradient-to-b from-slate-700 to-slate-900">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="mb-12"
                    >
                        <h2 className="text-4xl font-bold text-white text-center mb-4">
                            {filteredArticles.length} Article
                            {filteredArticles.length !== 1 ? "s" : ""} Found
                        </h2>
                        <div className="w-24 h-1 bg-gradient-to-r from-blue-400 to-purple-500 mx-auto"></div>
                    </motion.div>

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
                        <Pagination articles={articles} />
                    )}
                </div>
            </section>
        </MainLayout>
    );
};

const ArticleCard = ({ article, index, village }) => {
    const [ref, inView] = useInView({
        threshold: 0.1,
        triggerOnce: true,
    });

    return (
        <motion.article
            ref={ref}
            initial={{ opacity: 0, y: 50 }}
            animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ duration: 0.6, delay: index * 0.1 }}
            whileHover={{ y: -10, scale: 1.02 }}
            className="group bg-white/5 backdrop-blur-md rounded-2xl overflow-hidden border border-white/10 hover:border-white/30 transition-all duration-300"
        >
            <Link href={`/articles/${article.id}`}>
                <div className="relative h-48 overflow-hidden">
                    {article.cover_image_url ? (
                        <img
                            src={article.cover_image_url}
                            alt={article.title}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                            <span className="text-4xl text-white">📰</span>
                        </div>
                    )}
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-300" />

                    {/* Reading time badge */}
                    <div className="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                        {Math.ceil(
                            article.content?.replace(/<[^>]*>/g, "").split(" ")
                                .length / 200
                        ) || 5}{" "}
                        min read
                    </div>
                </div>

                <div className="p-6">
                    <div className="flex items-center mb-3">
                        {article.place && (
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                📍 {article.place.name}
                            </span>
                        )}
                    </div>

                    <h3 className="text-xl font-bold text-white mb-3 group-hover:text-blue-300 transition-colors duration-300 line-clamp-2">
                        {article.title}
                    </h3>

                    <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed">
                        {article.content
                            ?.replace(/<[^>]*>/g, "")
                            .substring(0, 150)}
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
            </Link>
        </motion.article>
    );
};

const Pagination = ({ articles }) => {
    const { current_page, last_page, per_page, total } = articles;

    return (
        <motion.div
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            transition={{ duration: 0.8 }}
            className="flex justify-center items-center mt-16 space-x-4"
        >
            {current_page > 1 && (
                <Link
                    href={`?page=${current_page - 1}`}
                    className="px-6 py-3 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-colors duration-300"
                >
                    ← Previous
                </Link>
            )}

            <div className="flex items-center space-x-2">
                {Array.from({ length: Math.min(5, last_page) }, (_, i) => {
                    const page = i + Math.max(1, current_page - 2);
                    if (page > last_page) return null;

                    return (
                        <Link
                            key={page}
                            href={`?page=${page}`}
                            className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 ${
                                page === current_page
                                    ? "bg-blue-500 text-white"
                                    : "bg-white/10 text-gray-300 hover:bg-white/20"
                            }`}
                        >
                            {page}
                        </Link>
                    );
                })}
            </div>

            {current_page < last_page && (
                <Link
                    href={`?page=${current_page + 1}`}
                    className="px-6 py-3 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-colors duration-300"
                >
                    Next →
                </Link>
            )}
        </motion.div>
    );
};

export default ArticlesPage;
