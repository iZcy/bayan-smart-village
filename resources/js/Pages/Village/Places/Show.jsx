// resources/js/Pages/Village/Places/Show.jsx
import React, { useEffect, useRef, useState } from "react";
import { Head, Link } from "@inertiajs/react";
import {
    motion,
    useScroll,
    useTransform,
    AnimatePresence,
} from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import { BaseCard } from "@/Components/Cards/Index";

export default function PlaceShowPage({ village, place }) {
    const containerRef = useRef(null);
    const audioRef = useRef(null);
    const { scrollYProgress } = useScroll({
        target: containerRef,
        offset: ["start start", "end start"],
    });

    // Firewatch-inspired hero parallax
    const heroY = useTransform(scrollYProgress, [0, 1], ["0%", "30%"]);
    const heroOpacity = useTransform(scrollYProgress, [0, 0.5], [1, 0]);
    const titleY = useTransform(scrollYProgress, [0, 0.3], ["0%", "-50%"]);

    const [activeSection, setActiveSection] = useState("overview");

    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [infoRef, infoInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({ threshold: 0.3 });

    const sections = [
        { id: "overview", label: "Overview", ref: heroRef },
        { id: "information", label: "Information", ref: infoRef },
        { id: "gallery", label: "Gallery", ref: galleryRef },
    ];

    // Audio management
    useEffect(() => {
        if (typeof window !== "undefined" && audioRef.current) {
            audioRef.current.volume = 0.3;
            audioRef.current.play().catch(() => {});
        }
        return () => {
            if (audioRef.current) {
                audioRef.current.pause();
            }
        };
    }, []);

    // Update active section based on scroll
    useEffect(() => {
        if (galleryInView) setActiveSection("gallery");
        else if (infoInView) setActiveSection("information");
        else if (heroInView) setActiveSection("overview");
    }, [heroInView, infoInView, galleryInView]);

    return (
        <MainLayout title={place.name} description={place.description}>
            <Head title={`${place.name} - ${village.name}`} />

            <audio ref={audioRef} loop>
                <source src="/audio/village-ambience.mp3" type="audio/mpeg" />
            </audio>

            <div
                ref={containerRef}
                className="min-h-screen bg-black text-white overflow-hidden"
            >
                {/* Enhanced Place-specific background */}
                <div
                    className="absolute inset-0 bg-cover bg-center"
                    style={{
                        backgroundImage: place.image_url
                            ? `url(${place.image_url})`
                            : "linear-gradient(45deg, #1a365d 0%, #2d5a87 50%, #4a90a4 100%)",
                    }}
                >
                    {!place.image_url && (
                        <svg
                            className="absolute inset-0 w-full h-full"
                            viewBox="0 0 1200 600"
                        >
                            {place.category?.type === "service" ? (
                                <>
                                    <motion.path
                                        initial={{ pathLength: 0 }}
                                        animate={{ pathLength: 1 }}
                                        transition={{ duration: 4, delay: 1 }}
                                        d="M0,600 L0,200 Q200,150 400,180 Q600,120 800,160 Q1000,100 1200,140 L1200,600 Z"
                                        fill="rgba(34, 197, 94, 0.2)"
                                    />
                                    <motion.circle
                                        cx="300"
                                        cy="150"
                                        r="50"
                                        fill="rgba(59, 130, 246, 0.2)"
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        transition={{ delay: 2, duration: 1.5 }}
                                    />
                                </>
                            ) : (
                                <>
                                    <motion.rect
                                        x="200"
                                        y="150"
                                        width="100"
                                        height="150"
                                        fill="rgba(249, 115, 22, 0.2)"
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        transition={{ delay: 1.5, duration: 2 }}
                                    />
                                    <motion.polygon
                                        points="800,100 900,200 700,200"
                                        fill="rgba(168, 85, 247, 0.2)"
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        transition={{ delay: 2, duration: 1.5 }}
                                    />
                                </>
                            )}
                        </svg>
                    )}
                </div>

                {/* Navigation Dots */}
                <div className="fixed right-8 top-1/2 transform -translate-y-1/2 z-50 space-y-4">
                    {sections.map((section) => (
                        <motion.button
                            key={section.id}
                            className={`w-3 h-3 rounded-full border-2 transition-all duration-300 ${
                                activeSection === section.id
                                    ? "bg-green-400 border-green-400"
                                    : "border-white/50 hover:border-white"
                            }`}
                            onClick={() => {
                                document
                                    .getElementById(section.id)
                                    ?.scrollIntoView({
                                        behavior: "smooth",
                                    });
                                setActiveSection(section.id);
                            }}
                            whileHover={{ scale: 1.2 }}
                            whileTap={{ scale: 0.8 }}
                        />
                    ))}
                </div>

                {/* Hero Section */}
                <motion.section
                    id="overview"
                    ref={heroRef}
                    className="relative h-screen flex items-center justify-center overflow-hidden"
                    style={{ y: heroY, opacity: heroOpacity }}
                >
                    {/* Dark overlay for readability */}
                    <div className="absolute inset-0 bg-black/50" />

                    {/* Hero Content */}
                    <motion.div
                        className="relative z-10 text-center max-w-4xl mx-auto px-6"
                        style={{ y: titleY }}
                    >
                        <motion.div
                            initial={{ opacity: 0, y: 100 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.3 }}
                        >
                            {/* Breadcrumb */}
                            <nav className="mb-6">
                                <motion.div
                                    className="flex items-center justify-center space-x-2 text-white/70"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    transition={{ delay: 0.8 }}
                                >
                                    <Link
                                        href="/"
                                        className="hover:text-white transition-colors"
                                    >
                                        {village.name}
                                    </Link>
                                    <span>/</span>
                                    <Link
                                        href="/places"
                                        className="hover:text-white transition-colors"
                                    >
                                        Places
                                    </Link>
                                    <span>/</span>
                                    <span className="text-white">
                                        {place.name}
                                    </span>
                                </motion.div>
                            </nav>

                            <motion.h1
                                className="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-white via-green-200 to-blue-200 bg-clip-text text-transparent"
                                initial={{ opacity: 0, scale: 0.8 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 1, delay: 0.5 }}
                            >
                                {place.name}
                            </motion.h1>

                            <motion.div
                                className="flex items-center justify-center space-x-6 mb-8"
                                initial={{ opacity: 0, y: 30 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8, delay: 0.7 }}
                            >
                                {place.category && (
                                    <span className="px-4 py-2 bg-gradient-to-r from-green-500 to-blue-500 rounded-full text-sm font-semibold">
                                        {place.category.name}
                                    </span>
                                )}
                                <span className="px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm">
                                    📍 {village.name}
                                </span>
                            </motion.div>

                            <motion.p
                                className="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto leading-relaxed"
                                initial={{ opacity: 0, y: 30 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8, delay: 0.9 }}
                            >
                                {place.description}
                            </motion.p>
                        </motion.div>
                    </motion.div>

                    {/* Scroll indicator */}
                    <motion.div
                        className="absolute bottom-8 left-1/2 transform -translate-x-1/2"
                        animate={{ y: [0, 10, 0] }}
                        transition={{ duration: 2, repeat: Infinity }}
                    >
                        <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center">
                            <div className="w-1 h-3 bg-white/50 rounded-full mt-2" />
                        </div>
                    </motion.div>
                </motion.section>

                {/* Information Section */}
                <motion.section
                    id="information"
                    ref={infoRef}
                    className="relative py-20 bg-gradient-to-br from-gray-900 via-black to-gray-800"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    transition={{ duration: 1 }}
                    viewport={{ once: true }}
                >
                    <div className="container mx-auto px-6">
                        <motion.div
                            className="max-w-6xl mx-auto"
                            variants={{
                                hidden: { opacity: 0 },
                                visible: {
                                    opacity: 1,
                                    transition: { staggerChildren: 0.2 },
                                },
                            }}
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true }}
                        >
                            <motion.h2
                                className="text-4xl md:text-6xl font-bold text-center mb-16 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent"
                                variants={{
                                    hidden: { opacity: 0, y: 50 },
                                    visible: { opacity: 1, y: 0 },
                                }}
                            >
                                Place Information
                            </motion.h2>

                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                                {/* Contact & Location Info */}
                                <motion.div
                                    className="space-y-8"
                                    variants={{
                                        hidden: { opacity: 0, x: -50 },
                                        visible: { opacity: 1, x: 0 },
                                    }}
                                >
                                    {place.phone_number && (
                                        <div className="flex items-center space-x-4 p-6 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10">
                                            <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                                                <span className="text-green-400 text-xl">
                                                    📞
                                                </span>
                                            </div>
                                            <div>
                                                <h3 className="text-lg font-semibold text-white">
                                                    Phone
                                                </h3>
                                                <a
                                                    href={`tel:${place.phone_number}`}
                                                    className="text-green-400 hover:text-green-300 transition-colors"
                                                >
                                                    {place.phone_number}
                                                </a>
                                            </div>
                                        </div>
                                    )}

                                    {place.address && (
                                        <div className="flex items-start space-x-4 p-6 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10">
                                            <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                                <span className="text-blue-400 text-xl">
                                                    📍
                                                </span>
                                            </div>
                                            <div>
                                                <h3 className="text-lg font-semibold text-white mb-2">
                                                    Address
                                                </h3>
                                                <p className="text-gray-300 leading-relaxed">
                                                    {place.address}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {place.latitude && place.longitude && (
                                        <div className="flex items-center space-x-4 p-6 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10">
                                            <div className="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center">
                                                <span className="text-purple-400 text-xl">
                                                    🗺️
                                                </span>
                                            </div>
                                            <div>
                                                <h3 className="text-lg font-semibold text-white">
                                                    Coordinates
                                                </h3>
                                                <p className="text-gray-300">
                                                    {place.latitude},{" "}
                                                    {place.longitude}
                                                </p>
                                                <a
                                                    href={`https://www.google.com/maps/search/?api=1&query=${place.latitude},${place.longitude}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-block mt-2 px-3 py-1 bg-purple-500/20 rounded-full text-xs hover:bg-purple-500/30 transition-colors"
                                                >
                                                    Open in Maps →
                                                </a>
                                            </div>
                                        </div>
                                    )}
                                </motion.div>

                                {/* Custom Fields */}
                                <motion.div
                                    className="space-y-6"
                                    variants={{
                                        hidden: { opacity: 0, x: 50 },
                                        visible: { opacity: 1, x: 0 },
                                    }}
                                >
                                    <h3 className="text-2xl font-bold text-white mb-6">
                                        Details
                                    </h3>

                                    {place.custom_fields &&
                                    Object.keys(place.custom_fields).length >
                                        0 ? (
                                        <div className="space-y-4">
                                            {Object.entries(
                                                place.custom_fields
                                            ).map(([key, value], index) => (
                                                <motion.div
                                                    key={key}
                                                    className="p-4 bg-gradient-to-r from-white/5 to-white/10 rounded-lg border border-white/10"
                                                    initial={{
                                                        opacity: 0,
                                                        y: 20,
                                                    }}
                                                    whileInView={{
                                                        opacity: 1,
                                                        y: 0,
                                                    }}
                                                    transition={{
                                                        delay: index * 0.1,
                                                    }}
                                                    viewport={{ once: true }}
                                                >
                                                    <h4 className="text-green-400 font-semibold capitalize mb-1">
                                                        {key.replace(/_/g, " ")}
                                                    </h4>
                                                    <p className="text-white">
                                                        {value}
                                                    </p>
                                                </motion.div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-gray-400 italic">
                                            No additional details available.
                                        </p>
                                    )}
                                </motion.div>
                            </div>
                        </motion.div>
                    </div>
                </motion.section>

                {/* Gallery Section */}
                {place.images && place.images.length > 0 && (
                    <motion.section
                        id="gallery"
                        ref={galleryRef}
                        className="relative py-20 bg-black"
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 1 }}
                        viewport={{ once: true }}
                    >
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl md:text-6xl font-bold text-center mb-16 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent"
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 1 }}
                                viewport={{ once: true }}
                            >
                                Gallery
                            </motion.h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                                {place.images.map((image, index) => (
                                    <motion.div
                                        key={image.id}
                                        className="relative group cursor-pointer overflow-hidden rounded-xl"
                                        initial={{ opacity: 0, scale: 0.8 }}
                                        whileInView={{ opacity: 1, scale: 1 }}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        whileHover={{ scale: 1.05 }}
                                        viewport={{ once: true }}
                                    >
                                        <img
                                            src={image.image_url}
                                            alt={
                                                image.caption ||
                                                `Gallery image ${index + 1}`
                                            }
                                            className="w-full h-64 object-cover"
                                        />
                                        <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                                            {image.caption && (
                                                <p className="text-white p-4 text-sm">
                                                    {image.caption}
                                                </p>
                                            )}
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.section>
                )}

                {/* SMEs Section */}
                {place.smes && place.smes.length > 0 && (
                    <motion.section
                        className="relative py-20 bg-gradient-to-t from-gray-900 to-black"
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 1 }}
                        viewport={{ once: true }}
                    >
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl md:text-6xl font-bold text-center mb-16 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent"
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 1 }}
                                viewport={{ once: true }}
                            >
                                Businesses Here
                            </motion.h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                                {place.smes.map((sme, index) => (
                                    <motion.div
                                        key={sme.id}
                                        initial={{ opacity: 0, y: 50 }}
                                        whileInView={{ opacity: 1, y: 0 }}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        viewport={{ once: true }}
                                    >
                                        <BaseCard
                                            className="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10 hover:border-green-400/50 transition-all duration-300"
                                            hoverEffects={true}
                                        >
                                            <div className="relative h-48 overflow-hidden">
                                                {sme.logo_url ? (
                                                    <img
                                                        src={sme.logo_url}
                                                        alt={sme.name}
                                                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                    />
                                                ) : (
                                                    <div className="w-full h-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                                                        <span className="text-4xl text-white">
                                                            🏪
                                                        </span>
                                                    </div>
                                                )}
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                            </div>
                                            <div className="p-6">
                                                <h3 className="text-xl font-bold text-white mb-2 group-hover:text-green-400 transition-colors">
                                                    {sme.name}
                                                </h3>
                                                <p className="text-gray-400 text-sm mb-4 line-clamp-2">
                                                    {sme.description}
                                                </p>
                                                {sme.contact_phone && (
                                                    <div className="flex items-center text-sm text-gray-400 mb-2">
                                                        <span className="mr-2">
                                                            📞
                                                        </span>
                                                        <span>
                                                            {sme.contact_phone}
                                                        </span>
                                                    </div>
                                                )}
                                                <span className="inline-block px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs">
                                                    {sme.type || "Business"}
                                                </span>
                                            </div>
                                        </BaseCard>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.section>
                )}

                {/* Articles Section */}
                {place.articles && place.articles.length > 0 && (
                    <motion.section
                        className="relative py-20 bg-gradient-to-t from-gray-900 to-black"
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 1 }}
                        viewport={{ once: true }}
                    >
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl md:text-6xl font-bold text-center mb-16 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent"
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 1 }}
                                viewport={{ once: true }}
                            >
                                Related Stories
                            </motion.h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                                {place.articles.map((article, index) => (
                                    <motion.div
                                        key={article.id}
                                        initial={{ opacity: 0, y: 50 }}
                                        whileInView={{ opacity: 1, y: 0 }}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        viewport={{ once: true }}
                                    >
                                        <Link
                                            href={`/articles/${article.slug}`}
                                        >
                                            <BaseCard
                                                className="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10 hover:border-blue-400/50 transition-all duration-300"
                                                hoverEffects={true}
                                            >
                                                <div className="relative h-48 overflow-hidden">
                                                    {article.cover_image_url ? (
                                                        <img
                                                            src={
                                                                article.cover_image_url
                                                            }
                                                            alt={article.title}
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                            <span className="text-4xl text-white">
                                                                📖
                                                            </span>
                                                        </div>
                                                    )}
                                                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                                </div>
                                                <div className="p-6">
                                                    <h3 className="text-xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors line-clamp-2">
                                                        {article.title}
                                                    </h3>
                                                    <p className="text-gray-400 text-sm mb-4 line-clamp-3">
                                                        {article.content
                                                            ?.replace(
                                                                /<[^>]*>/g,
                                                                ""
                                                            )
                                                            .substring(0, 120)}
                                                        ...
                                                    </p>
                                                    <div className="text-xs text-gray-500">
                                                        {new Date(
                                                            article.published_at ||
                                                                article.created_at
                                                        ).toLocaleDateString()}
                                                    </div>
                                                </div>
                                            </BaseCard>
                                        </Link>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.section>
                )}

                {/* Map Section */}
                {place.latitude && place.longitude && (
                    <motion.section
                        className="relative py-20 bg-black"
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 1 }}
                        viewport={{ once: true }}
                    >
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl md:text-6xl font-bold text-center mb-16 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent"
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 1 }}
                                viewport={{ once: true }}
                            >
                                Location
                            </motion.h2>

                            <motion.div
                                className="max-w-4xl mx-auto rounded-2xl overflow-hidden border border-white/20"
                                initial={{ opacity: 0, scale: 0.9 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.8 }}
                                viewport={{ once: true }}
                            >
                                <iframe
                                    src={`https://maps.google.com/maps?q=${place.latitude},${place.longitude}&z=15&output=embed`}
                                    width="100%"
                                    height="400"
                                    style={{ border: 0 }}
                                    loading="lazy"
                                    referrerPolicy="no-referrer-when-downgrade"
                                />
                            </motion.div>
                        </div>
                    </motion.section>
                )}
            </div>
        </MainLayout>
    );
}
