// resources/js/Pages/Village/Articles/Show.jsx
import React, { useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import MediaBackground from "@/Components/MediaBackground";
import HeroSection from "@/Components/HeroSection";
import { ArticleCard } from "@/Components/Cards/Index";

const ArticleShowPage = ({ village, article, relatedArticles }) => {
    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    // Village ambient music
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

    const getReadingTime = () => {
        const content = article.content?.replace(/<[^>]*>/g, "") || "";
        return Math.ceil(content.split(" ").length / 200) || 5;
    };

    return (
        <MainLayout title={article.title}>
            <Head title={`${article.title} - ${village?.name}`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/village-ambient.mp3" type="audio/mpeg" />
            </audio>

            {/* Media Background */}
            <MediaBackground
                context="article"
                village={village}
                enableControls={true}
                audioOnly={true}
                blur={true}
                controlsId="article-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Article Image Background Overlay */}
            <div
                className="fixed inset-0 bg-cover bg-center z-0"
                style={{
                    backgroundImage: article.cover_image_url
                        ? `url(${article.cover_image_url})`
                        : "none",
                }}
            >
                <div className="absolute inset-0 bg-black/50" />
            </div>

            {/* Hero Section */}
            <HeroSection
                title={article.title}
                subtitle={
                    article.content?.replace(/<[^>]*>/g, "").substring(0, 150) +
                        "..." || "A story from our village"
                }
                backgroundGradient="from-transparent to-transparent"
                parallax={true}
                scrollY={{ useTransform: useTransform }}
            >
                {/* Article meta in hero - Better Layout */}
                <motion.div
                    initial={{ opacity: 0, y: 50 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 1, delay: 1.5 }}
                    className="flex flex-col items-center gap-6 mt-8 max-w-2xl mx-auto"
                >
                    {/* Category Tags */}
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        {article.place && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/30">
                                📍 {article.place.name}
                            </span>
                        )}
                        {article.community && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/30">
                                👥 {article.community.name}
                            </span>
                        )}
                        {article.sme && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/30">
                                🏪 {article.sme.name}
                            </span>
                        )}
                    </div>

                    {/* Article Meta Info */}
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 1, delay: 1.8 }}
                        className="flex flex-col sm:flex-row items-center gap-4 text-white/90"
                    >
                        <time
                            dateTime={
                                article.published_at || article.created_at
                            }
                            className="flex items-center bg-black/30 backdrop-blur-sm px-4 py-2 rounded-full"
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
                                article.published_at || article.created_at
                            ).toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                            })}
                        </time>

                        <span className="flex items-center bg-black/30 backdrop-blur-sm px-4 py-2 rounded-full">
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
                            {getReadingTime()} min read
                        </span>
                    </motion.div>

                    {/* Read Story Button */}
                    <motion.button
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.8, delay: 2.2 }}
                        onClick={() => {
                            document
                                .getElementById("content")
                                .scrollIntoView({ behavior: "smooth" });
                        }}
                        className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300 border border-white/30"
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
                    </motion.button>
                </motion.div>
            </HeroSection>

            {/* Article Content */}
            <section id="content" className="relative py-20 bg-white">
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

                        {/* Article Header */}
                        <div className="mb-12">
                            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                                {article.title}
                            </h1>

                            {/* Article Meta */}
                            <div className="flex flex-wrap items-center gap-4 mb-8">
                                <div className="flex items-center text-gray-600">
                                    <svg
                                        className="w-5 h-5 mr-2"
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
                                        article.published_at ||
                                            article.created_at
                                    ).toLocaleDateString("en-US", {
                                        year: "numeric",
                                        month: "long",
                                        day: "numeric",
                                    })}
                                </div>

                                <div className="flex items-center text-gray-600">
                                    <svg
                                        className="w-5 h-5 mr-2"
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
                                    {getReadingTime()} minute read
                                </div>

                                {article.is_featured && (
                                    <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        ⭐ Featured
                                    </span>
                                )}
                            </div>

                            {/* Article Tags */}
                            <div className="flex flex-wrap gap-2 mb-8">
                                {article.place && (
                                    <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        📍 {article.place.name}
                                    </span>
                                )}
                                {article.community && (
                                    <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        👥 {article.community.name}
                                    </span>
                                )}
                                {article.sme && (
                                    <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        🏪 {article.sme.name}
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Featured Image */}
                        {article.cover_image_url && (
                            <motion.div
                                initial={{ opacity: 0, scale: 0.95 }}
                                animate={
                                    contentInView
                                        ? { opacity: 1, scale: 1 }
                                        : {}
                                }
                                transition={{ duration: 0.8, delay: 0.3 }}
                                className="mb-12"
                            >
                                <div className="aspect-video rounded-2xl overflow-hidden shadow-xl">
                                    <img
                                        src={article.cover_image_url}
                                        alt={article.title}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                            </motion.div>
                        )}

                        {/* Article Content */}
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={contentInView ? { opacity: 1 } : {}}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="prose prose-lg max-w-none"
                        >
                            <div
                                className="article-content text-gray-700 leading-relaxed"
                                dangerouslySetInnerHTML={{
                                    __html: article.content,
                                }}
                            />
                        </motion.div>

                        {/* Article Footer */}
                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            animate={contentInView ? { opacity: 1, y: 0 } : {}}
                            transition={{ duration: 0.8, delay: 0.8 }}
                            className="mt-16 pt-8 border-t border-gray-200"
                        >
                            <div className="flex items-center justify-between">
                                <div className="text-gray-600">
                                    <p className="text-lg font-semibold">
                                        Published in{" "}
                                        <span className="text-blue-600">
                                            {village?.name}
                                        </span>
                                    </p>
                                    {article.place && (
                                        <p className="mt-1 text-sm">
                                            About{" "}
                                            <strong>
                                                {article.place.name}
                                            </strong>
                                        </p>
                                    )}
                                    {article.community && (
                                        <p className="mt-1 text-sm">
                                            By{" "}
                                            <strong>
                                                {article.community.name}
                                            </strong>
                                        </p>
                                    )}
                                </div>

                                {/* Share buttons */}
                                <div className="flex space-x-4">
                                    <button className="p-3 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all duration-300">
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                        </svg>
                                    </button>
                                    <button className="p-3 text-gray-500 hover:text-blue-700 hover:bg-blue-50 rounded-full transition-all duration-300">
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                    </button>
                                    <button className="p-3 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-full transition-all duration-300">
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
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
                <section className="py-20 bg-gray-900 text-white relative">
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-3xl font-bold text-center mb-12 text-white"
                        >
                            More Stories from {village?.name}
                        </motion.h2>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                            {relatedArticles.map((relatedArticle, index) => (
                                <div
                                    key={relatedArticle.id}
                                    className="[&_*]:text-white [&_*]:border-white/30"
                                >
                                    <ArticleCard
                                        article={relatedArticle}
                                        index={index}
                                        village={village}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </MainLayout>
    );
};

export default ArticleShowPage;
