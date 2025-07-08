import React, { useState, useEffect, useRef } from "react";
import { Head } from "@inertiajs/react";
import {
    motion,
    useScroll,
    useTransform,
    useSpring,
    AnimatePresence,
} from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const VillageHomePage = ({
    village,
    places = { tourism: [], sme: [] },
    articles = [],
    gallery = [],
    products = [],
}) => {
    const tourismPlaces = places.tourism ?? [];
    const smePlaces = places.sme ?? [];
    console.log("places", places, typeof places, Array.isArray(places));

    const [currentSection, setCurrentSection] = useState(0);
    const [selectedTourismPlace, setSelectedTourismPlace] = useState(0);
    const [selectedSME, setSelectedSME] = useState(0);
    const [isPlaying, setIsPlaying] = useState(false);

    // Audio refs for each section
    const audioRefs = useRef([]);
    const { scrollY } = useScroll();

    // Parallax transforms
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const treesY = useTransform(scrollY, [0, 800], [0, -100]);
    const mountainsY = useTransform(scrollY, [0, 800], [0, -300]);

    // Section refs for intersection observer
    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [tourismRef, tourismInView] = useInView({ threshold: 0.3 });
    const [smeRef, smeInView] = useInView({ threshold: 0.3 });
    const [articlesRef, articlesInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({ threshold: 0.3 });

    // Auto-scroll tourism places
    useEffect(() => {
        const interval = setInterval(() => {
            if (tourismPlaces.length > 0) {
                setSelectedTourismPlace(
                    (prev) => (prev + 1) % tourismPlaces.length
                );
            }
        }, 4000);
        return () => clearInterval(interval);
    }, [tourismPlaces.length]);

    // Auto-scroll SME places
    useEffect(() => {
        const interval = setInterval(() => {
            if (smePlaces.length > 0) {
                setSelectedSME((prev) => (prev + 1) % smePlaces.length);
            }
        }, 3500);
        return () => clearInterval(interval);
    }, [smePlaces.length]);

    // Play different music based on section
    useEffect(() => {
        if (heroInView) setCurrentSection(0);
        else if (tourismInView) setCurrentSection(1);
        else if (smeInView) setCurrentSection(2);
        else if (articlesInView) setCurrentSection(3);
        else if (galleryInView) setCurrentSection(4);
    }, [heroInView, tourismInView, smeInView, articlesInView, galleryInView]);

    const toggleMusic = () => {
        setIsPlaying(!isPlaying);
        // In a real implementation, you'd control actual audio here
    };

    return (
        <MainLayout title={`Welcome to ${village?.name}`}>
            <Head title={`${village?.name} - Smart Village`} />

            {/* Music Control */}
            <motion.button
                onClick={toggleMusic}
                className="fixed top-20 right-6 z-50 bg-black/20 backdrop-blur-md text-white p-3 rounded-full hover:bg-black/30 transition-colors"
                whileHover={{ scale: 1.1 }}
                whileTap={{ scale: 0.9 }}
            >
                {isPlaying ? "🔊" : "🔇"}
            </motion.button>

            {/* Hero Section */}
            <section
                ref={heroRef}
                className="relative h-screen overflow-hidden"
            >
                {/* Background Mountains */}
                <motion.div
                    style={{ y: mountainsY }}
                    className="absolute inset-0 bg-gradient-to-b from-blue-400 via-green-300 to-green-500"
                >
                    <div className="absolute inset-0 opacity-30">
                        <svg viewBox="0 0 1200 600" className="w-full h-full">
                            <path
                                d="M0,600 L0,300 Q200,200 400,250 T800,200 Q1000,180 1200,220 L1200,600 Z"
                                fill="#2d5016"
                            />
                            <path
                                d="M0,600 L0,350 Q300,280 600,300 T1200,280 L1200,600 Z"
                                fill="#3d6b1f"
                            />
                            <path
                                d="M0,600 L0,400 Q400,350 800,380 T1200,360 L1200,600 Z"
                                fill="#4d7c2f"
                            />
                        </svg>
                    </div>
                </motion.div>

                {/* Trees - Left */}
                <motion.div
                    style={{ y: treesY }}
                    className="absolute left-0 bottom-0 w-1/4 h-full"
                >
                    <svg viewBox="0 0 300 600" className="w-full h-full">
                        <rect
                            x="140"
                            y="450"
                            width="20"
                            height="150"
                            fill="#4a5d23"
                        />
                        <circle cx="150" cy="420" r="80" fill="#5a7c30" />
                        <circle cx="120" cy="380" r="60" fill="#6a8c40" />
                        <circle cx="180" cy="390" r="65" fill="#5a7c30" />

                        <rect
                            x="80"
                            y="480"
                            width="15"
                            height="120"
                            fill="#4a5d23"
                        />
                        <circle cx="87" cy="460" r="50" fill="#6a8c40" />
                        <circle cx="70" cy="430" r="35" fill="#7a9c50" />

                        <rect
                            x="220"
                            y="470"
                            width="18"
                            height="130"
                            fill="#4a5d23"
                        />
                        <circle cx="229" cy="450" r="60" fill="#5a7c30" />
                        <circle cx="210" cy="420" r="40" fill="#6a8c40" />
                        <circle cx="250" cy="430" r="45" fill="#7a9c50" />
                    </svg>
                </motion.div>

                {/* Trees - Right */}
                <motion.div
                    style={{ y: treesY }}
                    className="absolute right-0 bottom-0 w-1/4 h-full"
                >
                    <svg viewBox="0 0 300 600" className="w-full h-full">
                        <rect
                            x="140"
                            y="460"
                            width="18"
                            height="140"
                            fill="#4a5d23"
                        />
                        <circle cx="149" cy="440" r="70" fill="#5a7c30" />
                        <circle cx="125" cy="410" r="50" fill="#6a8c40" />
                        <circle cx="170" cy="420" r="55" fill="#7a9c50" />

                        <rect
                            x="50"
                            y="490"
                            width="16"
                            height="110"
                            fill="#4a5d23"
                        />
                        <circle cx="58" cy="475" r="45" fill="#6a8c40" />
                        <circle cx="40" cy="450" r="30" fill="#7a9c50" />

                        <rect
                            x="240"
                            y="480"
                            width="20"
                            height="120"
                            fill="#4a5d23"
                        />
                        <circle cx="250" cy="460" r="65" fill="#5a7c30" />
                        <circle cx="225" cy="435" r="45" fill="#6a8c40" />
                        <circle cx="270" cy="445" r="40" fill="#7a9c50" />
                    </svg>
                </motion.div>

                {/* Hero Content */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 flex items-center justify-center text-center z-10"
                >
                    <div className="max-w-4xl px-6">
                        <motion.h1
                            initial={{ opacity: 0, y: 50 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="text-6xl md:text-8xl font-bold text-white mb-6 drop-shadow-lg"
                        >
                            {village?.name}
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-white/90 mb-8 drop-shadow-md max-w-2xl mx-auto"
                        >
                            {village?.description ||
                                "Discover the beauty and culture of our traditional village"}
                        </motion.p>
                        <motion.button
                            initial={{ opacity: 0, scale: 0.5 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.5 }}
                            whileHover={{ scale: 1.05, y: -5 }}
                            whileTap={{ scale: 0.95 }}
                            className="bg-white/20 backdrop-blur-md text-white px-8 py-4 rounded-full text-lg font-semibold border border-white/30 hover:bg-white/30 transition-all duration-300"
                        >
                            Explore Our Village
                        </motion.button>
                    </div>
                </motion.div>

                {/* Scroll Indicator */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 2, duration: 1 }}
                    className="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white"
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

            {/* Tourism Section */}
            <section
                ref={tourismRef}
                className="min-h-screen bg-gradient-to-b from-green-500 to-green-700 relative overflow-hidden py-20"
            >
                <div className="container mx-auto px-6 h-full">
                    <motion.h2
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-5xl font-bold text-white text-center mb-16"
                    >
                        Tourism Destinations
                    </motion.h2>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                        {/* Map - Left */}
                        <motion.div
                            initial={{ opacity: 0, x: -50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            className="bg-white/10 backdrop-blur-md rounded-2xl p-6 flex items-center justify-center"
                        >
                            <div className="w-full h-80 bg-green-400/30 rounded-xl flex items-center justify-center">
                                <div className="text-center text-white">
                                    <div className="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                        📍
                                    </div>
                                    <h3 className="text-xl font-semibold mb-2">
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.name || "Tourism Location"}
                                    </h3>
                                    <p className="text-sm opacity-75">
                                        Interactive map view
                                    </p>
                                </div>
                            </div>
                        </motion.div>

                        {/* Description - Center */}
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="flex flex-col justify-center"
                        >
                            <AnimatePresence mode="wait">
                                <motion.div
                                    key={selectedTourismPlace}
                                    initial={{ opacity: 0, x: 20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    exit={{ opacity: 0, x: -20 }}
                                    transition={{ duration: 0.5 }}
                                    className="text-white"
                                >
                                    <h3 className="text-3xl font-bold mb-4">
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.name || "Beautiful Destination"}
                                    </h3>
                                    <p className="text-lg opacity-90 mb-6 leading-relaxed">
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.description ||
                                            "Explore the natural beauty and cultural richness of this amazing destination."}
                                    </p>
                                    <div className="space-y-2">
                                        <div className="flex items-center">
                                            <span className="text-green-200">
                                                📍
                                            </span>
                                            <span className="ml-2">
                                                {tourismPlaces[
                                                    selectedTourismPlace
                                                ]?.address ||
                                                    "Village Location"}
                                            </span>
                                        </div>
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.phone_number && (
                                            <div className="flex items-center">
                                                <span className="text-green-200">
                                                    📞
                                                </span>
                                                <span className="ml-2">
                                                    {
                                                        tourismPlaces[
                                                            selectedTourismPlace
                                                        ].phone_number
                                                    }
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </motion.div>
                            </AnimatePresence>
                        </motion.div>

                        {/* Places List - Right */}
                        <motion.div
                            initial={{ opacity: 0, x: 50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8, delay: 0.4 }}
                            className="space-y-4"
                        >
                            {tourismPlaces.slice(0, 4).map((place, index) => (
                                <motion.div
                                    key={place.id}
                                    onClick={() =>
                                        setSelectedTourismPlace(index)
                                    }
                                    className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                        selectedTourismPlace === index
                                            ? "bg-white/20 backdrop-blur-md border border-white/30"
                                            : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                    }`}
                                    whileHover={{ scale: 1.02, y: -2 }}
                                    whileTap={{ scale: 0.98 }}
                                >
                                    <div className="flex items-center space-x-4">
                                        <div className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                                            {place.image_url ? (
                                                <img
                                                    src={place.image_url}
                                                    alt={place.name}
                                                    className="w-full h-full object-cover rounded-lg"
                                                />
                                            ) : (
                                                <span className="text-2xl">
                                                    🏞️
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex-1 text-white">
                                            <h4 className="font-semibold text-lg">
                                                {place.name}
                                            </h4>
                                            <p className="text-sm opacity-75 line-clamp-2">
                                                {place.category?.name}
                                            </p>
                                        </div>
                                        <div
                                            className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                                selectedTourismPlace === index
                                                    ? "bg-white"
                                                    : "bg-white/30"
                                            }`}
                                        />
                                    </div>
                                </motion.div>
                            ))}
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* SME Section */}
            <section
                ref={smeRef}
                className="min-h-screen bg-gradient-to-b from-amber-500 to-orange-600 relative overflow-hidden py-20"
            >
                <div className="container mx-auto px-6 h-full">
                    <motion.h2
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-5xl font-bold text-white text-center mb-16"
                    >
                        Local Businesses
                    </motion.h2>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                        {/* Places List - Left */}
                        <motion.div
                            initial={{ opacity: 0, x: -50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            className="space-y-4"
                        >
                            {smePlaces.slice(0, 4).map((place, index) => (
                                <motion.div
                                    key={place.id}
                                    onClick={() => setSelectedSME(index)}
                                    className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                        selectedSME === index
                                            ? "bg-white/20 backdrop-blur-md border border-white/30"
                                            : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                    }`}
                                    whileHover={{ scale: 1.02, y: -2 }}
                                    whileTap={{ scale: 0.98 }}
                                >
                                    <div className="flex items-center space-x-4">
                                        <div className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                                            {place.image_url ? (
                                                <img
                                                    src={place.image_url}
                                                    alt={place.name}
                                                    className="w-full h-full object-cover rounded-lg"
                                                />
                                            ) : (
                                                <span className="text-2xl">
                                                    🏪
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex-1 text-white">
                                            <h4 className="font-semibold text-lg">
                                                {place.name}
                                            </h4>
                                            <p className="text-sm opacity-75 line-clamp-2">
                                                {place.category?.name}
                                            </p>
                                        </div>
                                        <div
                                            className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                                selectedSME === index
                                                    ? "bg-white"
                                                    : "bg-white/30"
                                            }`}
                                        />
                                    </div>
                                </motion.div>
                            ))}
                        </motion.div>

                        {/* Description - Center */}
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="flex flex-col justify-center"
                        >
                            <AnimatePresence mode="wait">
                                <motion.div
                                    key={selectedSME}
                                    initial={{ opacity: 0, x: -20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    exit={{ opacity: 0, x: 20 }}
                                    transition={{ duration: 0.5 }}
                                    className="text-white"
                                >
                                    <h3 className="text-3xl font-bold mb-4">
                                        {smePlaces[selectedSME]?.name ||
                                            "Local Business"}
                                    </h3>
                                    <p className="text-lg opacity-90 mb-6 leading-relaxed">
                                        {smePlaces[selectedSME]?.description ||
                                            "Supporting local economy through quality products and services."}
                                    </p>
                                    <div className="space-y-2">
                                        <div className="flex items-center">
                                            <span className="text-orange-200">
                                                📍
                                            </span>
                                            <span className="ml-2">
                                                {smePlaces[selectedSME]
                                                    ?.address ||
                                                    "Village Location"}
                                            </span>
                                        </div>
                                        {smePlaces[selectedSME]
                                            ?.phone_number && (
                                            <div className="flex items-center">
                                                <span className="text-orange-200">
                                                    📞
                                                </span>
                                                <span className="ml-2">
                                                    {
                                                        smePlaces[selectedSME]
                                                            .phone_number
                                                    }
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </motion.div>
                            </AnimatePresence>
                        </motion.div>

                        {/* Map - Right */}
                        <motion.div
                            initial={{ opacity: 0, x: 50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8, delay: 0.4 }}
                            className="bg-white/10 backdrop-blur-md rounded-2xl p-6 flex items-center justify-center"
                        >
                            <div className="w-full h-80 bg-orange-400/30 rounded-xl flex items-center justify-center">
                                <div className="text-center text-white">
                                    <div className="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                        🏪
                                    </div>
                                    <h3 className="text-xl font-semibold mb-2">
                                        {smePlaces[selectedSME]?.name ||
                                            "Business Location"}
                                    </h3>
                                    <p className="text-sm opacity-75">
                                        Interactive map view
                                    </p>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Articles Section */}
            <section
                ref={articlesRef}
                className="min-h-screen bg-gradient-to-b from-blue-600 to-purple-700 py-20"
            >
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-5xl font-bold text-white text-center mb-16"
                    >
                        Village Stories
                    </motion.h2>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {articles?.slice(0, 6).map((article, index) => (
                            <motion.div
                                key={article.id}
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{
                                    duration: 0.6,
                                    delay: index * 0.1,
                                }}
                                whileHover={{ y: -10, scale: 1.02 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl overflow-hidden border border-white/20 hover:border-white/40 transition-all duration-300"
                            >
                                <div className="h-48 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                                    {article.cover_image_url ? (
                                        <img
                                            src={article.cover_image_url}
                                            alt={article.title}
                                            className="w-full h-full object-cover"
                                        />
                                    ) : (
                                        <span className="text-4xl text-white">
                                            📖
                                        </span>
                                    )}
                                </div>
                                <div className="p-6">
                                    <h3 className="text-xl font-bold text-white mb-3 line-clamp-2">
                                        {article.title}
                                    </h3>
                                    <p className="text-white/70 text-sm line-clamp-3 mb-4">
                                        {article.content
                                            ?.replace(/<[^>]*>/g, "")
                                            .substring(0, 120)}
                                        ...
                                    </p>
                                    <motion.button
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        className="text-blue-200 hover:text-white transition-colors duration-200 text-sm font-semibold"
                                    >
                                        Read More →
                                    </motion.button>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Gallery Section */}
            <section
                ref={galleryRef}
                className="min-h-screen bg-gradient-to-b from-purple-700 to-pink-600 py-20"
            >
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-5xl font-bold text-white text-center mb-16"
                    >
                        Village Gallery
                    </motion.h2>

                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {gallery?.slice(0, 12).map((image, index) => (
                            <motion.div
                                key={image.id}
                                initial={{ opacity: 0, scale: 0.8 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{
                                    duration: 0.5,
                                    delay: index * 0.05,
                                }}
                                whileHover={{ scale: 1.05, zIndex: 10 }}
                                className="aspect-square bg-white/10 backdrop-blur-md rounded-xl overflow-hidden border border-white/20"
                            >
                                {image.image_url ? (
                                    <img
                                        src={image.image_url}
                                        alt={image.caption || "Village photo"}
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-white/50">
                                        <span className="text-4xl">🖼️</span>
                                    </div>
                                )}
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
};

export default VillageHomePage;
