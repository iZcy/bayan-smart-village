import React, { useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform, useSpring } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const ArticleShowPage = ({ village, article, relatedArticles }) => {
    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Parallax effects for hero section
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroScale = useTransform(scrollY, [0, 800], [1, 1.1]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

    // Geometric elements animation
    const geometryY = useTransform(scrollY, [0, 1000], [0, -300]);
    const geometryRotate = useTransform(scrollY, [0, 1000], [0, 45]);

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    // Ambient village music
    useEffect(() => {
        if (audioRef.current) {
            audioRef.current.volume = 0.3;
            audioRef.current.play().catch(console.log);
        }

        return () => {
            if (audioRef.current) {
                audioRef.current.pause();
            }
        };
    }, []);

    return (
        <MainLayout title={article.title}>
            <Head title={`${article.title} - ${village?.name}`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/village-ambient.mp3" type="audio/mpeg" />
            </audio>

            {/* Enhanced Firewatch-Style Hero Section */}
            <section className="relative h-screen overflow-hidden">
                {/* Dynamic Background with Layered Parallax */}
                <motion.div
                    style={{ y: heroY, scale: heroScale }}
                    className="absolute inset-0"
                >
                    {article.cover_image_url ? (
                        <img
                            src={article.cover_image_url}
                            alt={article.title}
                            className="w-full h-full object-cover"
                        />
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-orange-400 via-red-500 to-pink-600 relative">
                            {/* Firewatch-style Mountain Silhouettes */}
                            <svg
                                viewBox="0 0 1200 600"
                                className="absolute inset-0 w-full h-full"
                            >
                                <defs>
                                    <linearGradient
                                        id="mountainGrad1"
                                        x1="0%"
                                        y1="0%"
                                        x2="100%"
                                        y2="100%"
                                    >
                                        <stop
                                            offset="0%"
                                            stopColor="rgba(26, 26, 26, 0.9)"
                                        />
                                        <stop
                                            offset="100%"
                                            stopColor="rgba(42, 42, 42, 0.7)"
                                        />
                                    </linearGradient>
                                    <linearGradient
                                        id="mountainGrad2"
                                        x1="0%"
                                        y1="0%"
                                        x2="100%"
                                        y2="100%"
                                    >
                                        <stop
                                            offset="0%"
                                            stopColor="rgba(42, 42, 42, 0.8)"
                                        />
                                        <stop
                                            offset="100%"
                                            stopColor="rgba(58, 58, 58, 0.6)"
                                        />
                                    </linearGradient>
                                </defs>

                                {/* Back mountains */}
                                <motion.path
                                    initial={{ pathLength: 0, opacity: 0 }}
                                    animate={{ pathLength: 1, opacity: 1 }}
                                    transition={{ duration: 3, delay: 0.5 }}
                                    d="M0,600 L0,180 Q200,120 400,150 Q600,100 800,140 Q1000,80 1200,120 L1200,600 Z"
                                    fill="url(#mountainGrad1)"
                                />

                                {/* Middle mountains */}
                                <motion.path
                                    initial={{ pathLength: 0, opacity: 0 }}
                                    animate={{ pathLength: 1, opacity: 1 }}
                                    transition={{ duration: 3, delay: 1 }}
                                    d="M0,600 L0,280 Q300,220 600,250 Q900,200 1200,230 L1200,600 Z"
                                    fill="url(#mountainGrad2)"
                                />

                                {/* Front mountains */}
                                <motion.path
                                    initial={{ pathLength: 0, opacity: 0 }}
                                    animate={{ pathLength: 1, opacity: 1 }}
                                    transition={{ duration: 3, delay: 1.5 }}
                                    d="M0,600 L0,380 Q400,320 800,350 Q1000,330 1200,340 L1200,600 Z"
                                    fill="rgba(58, 58, 58, 0.9)"
                                />
                            </svg>

                            {/* Firewatch Tower */}
                            <motion.div
                                initial={{ y: 50, opacity: 0 }}
                                animate={{ y: 0, opacity: 0.8 }}
                                transition={{ duration: 2, delay: 2 }}
                                className="absolute bottom-32 right-1/4 w-1 h-16 bg-black/60"
                            >
                                <div className="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-full w-6 h-6 bg-black/60 rounded-sm" />
                            </motion.div>

                            {/* Floating particles */}
                            {[...Array(8)].map((_, i) => (
                                <motion.div
                                    key={i}
                                    className="absolute w-1 h-1 bg-white/30 rounded-full"
                                    style={{
                                        left: `${Math.random() * 100}%`,
                                        top: `${Math.random() * 60}%`,
                                    }}
                                    animate={{
                                        y: [0, -20, 0],
                                        opacity: [0.3, 1, 0.3],
                                        scale: [1, 1.5, 1],
                                    }}
                                    transition={{
                                        duration: 3 + Math.random() * 2,
                                        repeat: Infinity,
                                        delay: Math.random() * 2,
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </motion.div>

                {/* Enhanced Geometric Elements */}
                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute top-20 right-20 w-32 h-32 opacity-20"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <motion.polygon
                            points="50,15 90,85 10,85"
                            fill="currentColor"
                            opacity="0.6"
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            transition={{ delay: 2.5, duration: 1 }}
                        />
                        <motion.circle
                            cx="50"
                            cy="50"
                            r="20"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            initial={{ pathLength: 0 }}
                            animate={{ pathLength: 1 }}
                            transition={{ delay: 3, duration: 1.5 }}
                        />
                        <motion.rect
                            x="35"
                            y="35"
                            width="30"
                            height="30"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="1"
                            initial={{ rotate: 45, scale: 0 }}
                            animate={{ rotate: 0, scale: 1 }}
                            transition={{ delay: 3.5, duration: 1 }}
                        />
                    </svg>
                </motion.div>

                {/* Additional geometric elements */}
                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute bottom-32 left-16 w-24 h-24 opacity-15"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <motion.polygon
                            points="50,10 80,30 80,70 50,90 20,70 20,30"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            initial={{ pathLength: 0 }}
                            animate={{ pathLength: 1 }}
                            transition={{ delay: 4, duration: 2 }}
                        />
                    </svg>
                </motion.div>

                {/* Atmospheric overlay */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 1, duration: 2 }}
                    className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"
                />

                {/* Enhanced Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                    <div className="max-w-4xl px-6">
                        {/* Breadcrumb with animation */}
                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="mb-6"
                        >
                            {article.place && (
                                <motion.span
                                    className="inline-block px-4 py-2 bg-white/10 backdrop-blur-md text-white rounded-full text-sm font-medium mb-4 border border-white/20"
                                    whileHover={{
                                        scale: 1.05,
                                        backgroundColor:
                                            "rgba(255,255,255,0.2)",
                                    }}
                                    transition={{ duration: 0.3 }}
                                >
                                    📍 {article.place.name}
                                </motion.span>
                            )}
                        </motion.div>

                        {/* Enhanced Title */}
                        <motion.h1
                            initial={{ opacity: 0, y: 80, scale: 0.9 }}
                            animate={{ opacity: 1, y: 0, scale: 1 }}
                            transition={{
                                duration: 1.2,
                                delay: 0.8,
                                type: "spring",
                            }}
                            className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                        >
                            <motion.span
                                className="inline-block"
                                whileHover={{
                                    scale: 1.05,
                                    textShadow:
                                        "0 0 20px rgba(255,255,255,0.5)",
                                }}
                                transition={{ duration: 0.3 }}
                            >
                                {article.title}
                            </motion.span>
                        </motion.h1>

                        {/* Enhanced Meta Information */}
                        <motion.div
                            initial={{ opacity: 0, y: 60 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1.1 }}
                            className="flex items-center justify-center space-x-8 text-white/80 mb-8"
                        >
                            <motion.time
                                dateTime={article.created_at}
                                className="flex items-center bg-black/30 backdrop-blur-sm px-4 py-2 rounded-full"
                                whileHover={{
                                    scale: 1.05,
                                    backgroundColor: "rgba(0,0,0,0.5)",
                                }}
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                {new Date(
                                    article.created_at
                                ).toLocaleDateString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric",
                                })}
                            </motion.time>

                            <motion.span
                                className="flex items-center bg-black/30 backdrop-blur-sm px-4 py-2 rounded-full"
                                whileHover={{
                                    scale: 1.05,
                                    backgroundColor: "rgba(0,0,0,0.5)",
                                }}
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                {Math.ceil(
                                    article.content
                                        ?.replace(/<[^>]*>/g, "")
                                        .split(" ").length / 200
                                ) || 5}{" "}
                                min read
                            </motion.span>
                        </motion.div>

                        {/* Enhanced CTA Button */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.4 }}
                            className="mt-8"
                        >
                            <motion.button
                                onClick={() => {
                                    document
                                        .getElementById("content")
                                        .scrollIntoView({
                                            behavior: "smooth",
                                        });
                                }}
                                className="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-white/10 to-white/20 backdrop-blur-md text-white rounded-full border border-white/30 overflow-hidden"
                                whileHover={{
                                    scale: 1.05,
                                    boxShadow:
                                        "0 20px 40px rgba(255,255,255,0.1)",
                                }}
                                whileTap={{ scale: 0.95 }}
                            >
                                {/* Button background animation */}
                                <motion.div
                                    className="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-red-500/20"
                                    initial={{ x: "-100%" }}
                                    whileHover={{ x: "100%" }}
                                    transition={{ duration: 0.6 }}
                                />

                                <span className="relative z-10 font-semibold">
                                    Read Story
                                </span>

                                <motion.svg
                                    className="w-5 h-5 ml-2 relative z-10"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    animate={{ y: [0, 3, 0] }}
                                    transition={{
                                        duration: 1.5,
                                        repeat: Infinity,
                                    }}
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                    />
                                </motion.svg>
                            </motion.button>
                        </motion.div>
                    </div>
                </div>

                {/* Enhanced Scroll Indicator */}
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 2, duration: 1 }}
                    className="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white/70"
                >
                    <motion.div
                        animate={{ y: [0, 10, 0] }}
                        transition={{ duration: 2, repeat: Infinity }}
                        className="flex flex-col items-center"
                    >
                        <span className="text-sm mb-2 font-medium">
                            Scroll to explore
                        </span>
                        <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center relative overflow-hidden">
                            <motion.div
                                animate={{ y: [0, 16, 0] }}
                                transition={{ duration: 2, repeat: Infinity }}
                                className="w-1 h-3 bg-white/70 rounded-full mt-2"
                            />
                        </div>
                    </motion.div>
                </motion.div>
            </section>

            {/* Article Content */}
            <section id="content" className="relative py-20 bg-white">
                {/* Geometric decorations */}
                <div className="absolute top-0 left-0 w-64 h-64 opacity-5">
                    <svg
                        viewBox="0 0 200 200"
                        className="w-full h-full text-gray-900"
                    >
                        <defs>
                            <pattern
                                id="grid"
                                width="20"
                                height="20"
                                patternUnits="userSpaceOnUse"
                            >
                                <path
                                    d="M 20 0 L 0 0 0 20"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1"
                                />
                            </pattern>
                        </defs>
                        <rect width="200" height="200" fill="url(#grid)" />
                    </svg>
                </div>

                <div className="container mx-auto px-6 relative z-10">
                    <motion.div
                        ref={contentRef}
                        initial={{ opacity: 0, y: 50 }}
                        animate={contentInView ? { opacity: 1, y: 0 } : {}}
                        transition={{ duration: 1 }}
                        className="max-w-4xl mx-auto"
                    >
                        {/* Article Navigation */}
                        <nav className="mb-12">
                            <Link
                                href="/articles"
                                className="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-300"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                                Back to Articles
                            </Link>
                        </nav>

                        {/* Content Body */}
                        <div className="prose prose-lg max-w-none">
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={contentInView ? { opacity: 1 } : {}}
                                transition={{ duration: 1, delay: 0.3 }}
                                className="article-content"
                                dangerouslySetInnerHTML={{
                                    __html: article.content,
                                }}
                            />
                        </div>

                        {/* Article Meta */}
                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            animate={contentInView ? { opacity: 1, y: 0 } : {}}
                            transition={{ duration: 0.8, delay: 0.6 }}
                            className="mt-16 pt-8 border-t border-gray-200"
                        >
                            <div className="flex items-center justify-between">
                                <div className="text-gray-600">
                                    <p>
                                        Published in{" "}
                                        <strong>{village?.name}</strong>
                                    </p>
                                    {article.place && (
                                        <p className="mt-1">
                                            About{" "}
                                            <strong>
                                                {article.place.name}
                                            </strong>
                                        </p>
                                    )}
                                </div>

                                {/* Share buttons */}
                                <div className="flex space-x-4">
                                    <button className="p-2 text-gray-500 hover:text-blue-600 transition-colors duration-300">
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                        </svg>
                                    </button>
                                    <button className="p-2 text-gray-500 hover:text-blue-700 transition-colors duration-300">
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </motion.div>
                </div>
            </section>

            {/* Related Articles */}
            {relatedArticles && relatedArticles.length > 0 && (
                <section className="py-20 bg-gray-50">
                    <div className="container mx-auto px-6">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-3xl font-bold text-center mb-12"
                        >
                            More Stories from {village?.name}
                        </motion.h2>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                            {relatedArticles.map((relatedArticle, index) => (
                                <motion.article
                                    key={relatedArticle.id}
                                    initial={{ opacity: 0, y: 50 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    transition={{
                                        duration: 0.6,
                                        delay: index * 0.1,
                                    }}
                                    whileHover={{ y: -10, scale: 1.02 }}
                                    className="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    <Link
                                        href={`/articles/${relatedArticle.id}`}
                                    >
                                        <div className="aspect-video bg-gradient-to-br from-blue-400 to-purple-500 relative overflow-hidden">
                                            {relatedArticle.cover_image_url ? (
                                                <img
                                                    src={
                                                        relatedArticle.cover_image_url
                                                    }
                                                    alt={relatedArticle.title}
                                                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center">
                                                    <span className="text-4xl text-white">
                                                        📖
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="p-6">
                                            <h3 className="text-xl font-bold mb-3 group-hover:text-blue-600 transition-colors duration-300 line-clamp-2">
                                                {relatedArticle.title}
                                            </h3>
                                            <p className="text-gray-600 text-sm line-clamp-2">
                                                {relatedArticle.content
                                                    ?.replace(/<[^>]*>/g, "")
                                                    .substring(0, 120)}
                                                ...
                                            </p>
                                            <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
                                                <time
                                                    dateTime={
                                                        relatedArticle.created_at
                                                    }
                                                >
                                                    {new Date(
                                                        relatedArticle.created_at
                                                    ).toLocaleDateString()}
                                                </time>
                                                <span className="group-hover:text-blue-600 transition-colors duration-300">
                                                    Read more →
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                </motion.article>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </MainLayout>
    );
};

export default ArticleShowPage;
