import React, { useEffect, useRef, useState } from "react";
import { Head, Link } from "@inertiajs/react";
import {
    motion,
    useScroll,
    useTransform,
    useSpring,
    AnimatePresence,
} from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "../Layouts/MainLayout";

export default function PlaceShow({ village, place, relatedPlaces }) {
    const containerRef = useRef(null);
    const { scrollYProgress } = useScroll({
        target: containerRef,
        offset: ["start start", "end start"],
    });

    // Firewatch-inspired hero parallax
    const heroY = useTransform(scrollYProgress, [0, 1], ["0%", "30%"]);
    const heroOpacity = useTransform(scrollYProgress, [0, 0.5], [1, 0]);
    const titleY = useTransform(scrollYProgress, [0, 0.3], ["0%", "-50%"]);

    // Geometric elements from Enroute Health inspiration
    const [geometryElements, setGeometryElements] = useState([]);
    const [activeSection, setActiveSection] = useState("overview");

    useEffect(() => {
        // Generate random geometric elements
        const elements = Array.from({ length: 8 }, (_, i) => ({
            id: i,
            x: Math.random() * 100,
            y: Math.random() * 100,
            size: Math.random() * 60 + 20,
            rotation: Math.random() * 360,
            delay: Math.random() * 2,
            type: ["circle", "triangle", "square"][
                Math.floor(Math.random() * 3)
            ],
        }));
        setGeometryElements(elements);
    }, []);

    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [infoRef, infoInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({ threshold: 0.3 });
    const [relatedRef, relatedInView] = useInView({ threshold: 0.3 });

    const sections = [
        { id: "overview", label: "Overview", ref: heroRef },
        { id: "information", label: "Information", ref: infoRef },
        { id: "gallery", label: "Gallery", ref: galleryRef },
        { id: "related", label: "Related Places", ref: relatedRef },
    ];

    // Audio management
    const audioRef = useRef(null);
    useEffect(() => {
        if (typeof window !== "undefined" && audioRef.current) {
            audioRef.current.volume = 0.3;
            audioRef.current.play().catch(() => {}); // Auto-play with error handling
        }
        return () => {
            if (audioRef.current) {
                audioRef.current.pause();
            }
        };
    }, []);

    return (
        <MainLayout title={place.name} description={place.description}>
            <Head>
                <title>
                    {place.name} - {village.name}
                </title>
                <meta
                    name="description"
                    content={place.description?.substring(0, 160)}
                />
            </Head>

            <audio ref={audioRef} loop>
                <source src="/audio/village-ambience.mp3" type="audio/mpeg" />
            </audio>

            <div
                ref={containerRef}
                className="min-h-screen bg-black text-white overflow-hidden"
            >
                {/* Geometric Background Elements */}
                <div className="fixed inset-0 pointer-events-none z-0">
                    {geometryElements.map((element) => (
                        <motion.div
                            key={element.id}
                            className="absolute opacity-10"
                            style={{
                                left: `${element.x}%`,
                                top: `${element.y}%`,
                                width: `${element.size}px`,
                                height: `${element.size}px`,
                            }}
                            initial={{
                                scale: 0,
                                rotate: 0,
                                opacity: 0,
                            }}
                            animate={{
                                scale: 1,
                                rotate: element.rotation,
                                opacity: [0, 0.1, 0.05, 0.1],
                            }}
                            transition={{
                                duration: 4,
                                delay: element.delay,
                                repeat: Infinity,
                                repeatType: "reverse",
                            }}
                        >
                            {element.type === "circle" && (
                                <div className="w-full h-full rounded-full border-2 border-green-400" />
                            )}
                            {element.type === "triangle" && (
                                <div className="w-0 h-0 border-l-[30px] border-r-[30px] border-b-[52px] border-l-transparent border-r-transparent border-b-blue-400" />
                            )}
                            {element.type === "square" && (
                                <div className="w-full h-full border-2 border-purple-400 rotate-45" />
                            )}
                        </motion.div>
                    ))}
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
                                section.ref.current?.scrollIntoView({
                                    behavior: "smooth",
                                });
                                setActiveSection(section.id);
                            }}
                            whileHover={{ scale: 1.2 }}
                            whileTap={{ scale: 0.8 }}
                        />
                    ))}
                </div>

                {/* Firewatch-inspired Hero Section */}
                <motion.section
                    ref={heroRef}
                    className="relative h-screen flex items-center justify-center overflow-hidden"
                    style={{ y: heroY, opacity: heroOpacity }}
                >
                    {/* Background Image with Parallax */}
                    <div
                        className="absolute inset-0 bg-cover bg-center"
                        style={{
                            backgroundImage: place.image_url
                                ? `url(${place.image_url})`
                                : "linear-gradient(45deg, #1a365d 0%, #2d5a87 50%, #4a90a4 100%)",
                        }}
                    />

                    {/* Dark overlay for readability */}
                    <div className="absolute inset-0 bg-black/50" />

                    {/* Firewatch-style geometric overlay */}
                    <div className="absolute inset-0">
                        <svg
                            className="w-full h-full"
                            viewBox="0 0 100 100"
                            preserveAspectRatio="none"
                        >
                            <motion.polygon
                                points="0,0 0,60 30,80 0,100 100,100 100,0"
                                fill="rgba(74, 144, 164, 0.1)"
                                initial={{ pathLength: 0 }}
                                animate={{ pathLength: 1 }}
                                transition={{ duration: 2, delay: 0.5 }}
                            />
                            <motion.polygon
                                points="70,0 100,0 100,40 85,60"
                                fill="rgba(45, 90, 135, 0.15)"
                                initial={{ pathLength: 0 }}
                                animate={{ pathLength: 1 }}
                                transition={{ duration: 2, delay: 1 }}
                            />
                        </svg>
                    </div>

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
                                <span className="px-4 py-2 bg-gradient-to-r from-green-500 to-blue-500 rounded-full text-sm font-semibold">
                                    {place.category?.name}
                                </span>
                                {place.village && (
                                    <span className="px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm">
                                        📍 {place.village.name}
                                    </span>
                                )}
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
                                    transition: {
                                        staggerChildren: 0.2,
                                    },
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

                {/* Related Places Section */}
                {relatedPlaces && relatedPlaces.length > 0 && (
                    <motion.section
                        ref={relatedRef}
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
                                Related Places
                            </motion.h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
                                {relatedPlaces.map((relatedPlace, index) => (
                                    <motion.div
                                        key={relatedPlace.id}
                                        initial={{ opacity: 0, y: 50 }}
                                        whileInView={{ opacity: 1, y: 0 }}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        viewport={{ once: true }}
                                    >
                                        <Link
                                            href={`/places/${relatedPlace.id}`}
                                            className="block group"
                                        >
                                            <motion.div
                                                className="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10 hover:border-green-400/50 transition-all duration-300"
                                                whileHover={{ y: -5 }}
                                            >
                                                <div className="relative h-48 overflow-hidden">
                                                    <img
                                                        src={
                                                            relatedPlace.image_url ||
                                                            "/images/place-placeholder.jpg"
                                                        }
                                                        alt={relatedPlace.name}
                                                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                    />
                                                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                                </div>
                                                <div className="p-6">
                                                    <h3 className="text-xl font-bold text-white mb-2 group-hover:text-green-400 transition-colors">
                                                        {relatedPlace.name}
                                                    </h3>
                                                    <p className="text-gray-400 text-sm mb-4 line-clamp-2">
                                                        {
                                                            relatedPlace.description
                                                        }
                                                    </p>
                                                    <span className="inline-block px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs">
                                                        {
                                                            relatedPlace
                                                                .category?.name
                                                        }
                                                    </span>
                                                </div>
                                            </motion.div>
                                        </Link>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.section>
                )}
            </div>
        </MainLayout>
    );
}
