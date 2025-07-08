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

            {/* Hero Section - Firewatch Style */}
            <section className="relative h-screen overflow-hidden">
                {/* Background Image with Parallax */}
                <motion.div
                    style={{ y: heroY, scale: heroScale, opacity: heroOpacity }}
                    className="absolute inset-0"
                >
                    {article.cover_image_url ? (
                        <img
                            src={article.cover_image_url}
                            alt={article.title}
                            className="w-full h-full object-cover"
                        />
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-orange-400 via-red-500 to-pink-600">
                            <div className="absolute inset-0 opacity-20">
                                <svg
                                    viewBox="0 0 1200 600"
                                    className="w-full h-full"
                                >
                                    {/* Mountain silhouettes */}
                                    <path
                                        d="M0,600 L0,200 Q300,150 600,180 T1200,160 L1200,600 Z"
                                        fill="#1a1a1a"
                                        opacity="0.8"
                                    />
                                    <path
                                        d="M0,600 L0,300 Q400,250 800,270 T1200,250 L1200,600 Z"
                                        fill="#2a2a2a"
                                        opacity="0.6"
                                    />
                                    <path
                                        d="M0,600 L0,400 Q500,350 1000,370 T1200,350 L1200,600 Z"
                                        fill="#3a3a3a"
                                        opacity="0.4"
                                    />
                                </svg>
                            </div>
                        </div>
                    )}
                </motion.div>

                {/* Geometric Elements - Enroute Health Style */}
                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute top-20 right-20 w-32 h-32 opacity-20"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <polygon
                            points="50,15 90,85 10,85"
                            fill="currentColor"
                            opacity="0.6"
                        />
                        <circle
                            cx="50"
                            cy="50"
                            r="20"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                        />
                    </svg>
                </motion.div>

                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute bottom-32 left-16 w-24 h-24 opacity-15"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <rect
                            x="20"
                            y="20"
                            width="60"
                            height="60"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="3"
                        />
                        <rect
                            x="35"
                            y="35"
                            width="30"
                            height="30"
                            fill="currentColor"
                            opacity="0.4"
                        />
                    </svg>
                </motion.div>

                {/* Overlay gradient */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/30" />

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                    <div className="max-w-4xl px-6">
                        <motion.div
                            initial={{ opacity: 0, y: 100 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1.2, delay: 0.5 }}
                            className="mb-6"
                        >
                            {article.place && (
                                <span className="inline-block px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium mb-4">
                                    📍 {article.place.name}
                                </span>
                            )}
                        </motion.div>

                        <motion.h1
                            initial={{ opacity: 0, y: 80 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.8 }}
                            className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                        >
                            {article.title}
                        </motion.h1>

                        <motion.div
                            initial={{ opacity: 0, y: 60 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1.1 }}
                            className="flex items-center justify-center space-x-6 text-white/80"
                        >
                            <time
                                dateTime={article.created_at}
                                className="flex items-center"
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
                            </time>
                            <span className="flex items-center">
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
                            </span>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.4 }}
                            className="mt-8"
                        >
                            <button
                                onClick={() => {
                                    document
                                        .getElementById("content")
                                        .scrollIntoView({
                                            behavior: "smooth",
                                        });
                                }}
                                className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300"
                            >
                                Read Story
                                <svg
                                    className="w-5 h-5 ml-2 group-hover:translate-y-1 transition-transform duration-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                    />
                                </svg>
                            </button>
                        </motion.div>
                    </div>
                </div>
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
