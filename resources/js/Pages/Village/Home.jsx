// resources/js/Pages/Village/Home.jsx
import React, { useState, useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import {
    motion,
    useScroll,
    useTransform,
    AnimatePresence,
} from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import { ArticleCard, ProductCard, PlaceCard } from "@/Components/Cards/Index";

const Home = ({
    village,
    featuredPlaces = [],
    featuredProducts = [],
    featuredArticles = [],
    featuredImages = [],
}) => {
    const [currentSection, setCurrentSection] = useState(0);
    const [selectedTourismPlace, setSelectedTourismPlace] = useState(0);
    const [selectedSME, setSelectedSME] = useState(0);
    const [isPlaying, setIsPlaying] = useState(true);
    const [isUserInteracting, setIsUserInteracting] = useState(false);
    const [interactionTimeout, setInteractionTimeout] = useState(null);

    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Separate places by type - ensure we have arrays
    const tourismPlaces =
        featuredPlaces?.filter((place) => place.category?.type === "service") ||
        [];
    const smePlaces =
        featuredPlaces?.filter((place) => place.category?.type === "product") ||
        [];

    // Audio management
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

    // User interaction detection
    const handleUserInteraction = () => {
        setIsUserInteracting(true);
        if (interactionTimeout) {
            clearTimeout(interactionTimeout);
        }
        const timeout = setTimeout(() => {
            setIsUserInteracting(false);
        }, 5000);
        setInteractionTimeout(timeout);
    };

    // Auto-scroll for tourism places
    useEffect(() => {
        if (isUserInteracting || tourismPlaces.length === 0) return;
        const interval = setInterval(() => {
            setSelectedTourismPlace(
                (prev) => (prev + 1) % tourismPlaces.length
            );
        }, 5000);
        return () => clearInterval(interval);
    }, [tourismPlaces.length, isUserInteracting]);

    // Auto-scroll for SME places
    useEffect(() => {
        if (isUserInteracting || smePlaces.length === 0) return;
        const interval = setInterval(() => {
            setSelectedSME((prev) => (prev + 1) % smePlaces.length);
        }, 5000);
        return () => clearInterval(interval);
    }, [smePlaces.length, isUserInteracting]);

    // Section refs
    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [tourismRef, tourismInView] = useInView({ threshold: 0.3 });
    const [smeRef, smeInView] = useInView({ threshold: 0.3 });
    const [productsRef, productsInView] = useInView({ threshold: 0.3 });
    const [articlesRef, articlesInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({ threshold: 0.1 });

    // Color overlay based on scroll
    const colorOverlay = useTransform(
        scrollY,
        [0, 800, 1600, 2400, 3200, 4000],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.2))",
            "linear-gradient(to bottom, rgba(34,197,94,0.4), rgba(34,197,94,0.6))",
            "linear-gradient(to bottom, rgba(245,158,11,0.4), rgba(234,88,12,0.6))",
            "linear-gradient(to bottom, rgba(79,70,229,0.4), rgba(29,78,216,0.6))",
            "linear-gradient(to bottom, rgba(37,99,235,0.4), rgba(126,34,206,0.6))",
            "linear-gradient(to bottom, rgba(126,34,206,0.4), rgba(219,39,119,0.6))",
        ]
    );

    const toggleMusic = () => {
        setIsPlaying((prev) => {
            const newState = !prev;
            if (audioRef.current) {
                if (newState) {
                    audioRef.current.play().catch(console.log);
                } else {
                    audioRef.current.pause();
                }
            }
            return newState;
        });
    };

    return (
        <MainLayout title={`Welcome to ${village?.name}`}>
            <Head title={`${village?.name} - Smart Village`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/sasakbacksong.mp3" type="audio/mpeg" />
            </audio>

            {/* Video Background */}
            <div className="fixed inset-0 z-0">
                <video
                    autoPlay
                    muted
                    loop
                    playsInline
                    className="w-full h-full object-cover"
                >
                    <source src="/video/videobackground.mp4" type="video/mp4" />
                </video>
                <div className="absolute inset-0 bg-black/20" />
            </div>

            {/* Color Overlay */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{ background: colorOverlay }}
            />

            {/* Music Control */}
            <motion.button
                onClick={toggleMusic}
                className="fixed top-20 right-6 z-[60] bg-black/20 backdrop-blur-md text-white p-3 rounded-full hover:bg-black/30 transition-colors"
                whileHover={{ scale: 1.1 }}
                whileTap={{ scale: 0.9 }}
            >
                {isPlaying ? "🔊" : "🔇"}
            </motion.button>

            {/* Hero Section */}
            <section
                ref={heroRef}
                className="relative h-screen overflow-hidden z-10"
            >
                <div className="absolute inset-0 backdrop-blur-[1px]" />
                <HeroSection
                    title={village?.name || "Smart Village"}
                    subtitle={
                        village?.description ||
                        "Discover the beauty and culture of our traditional village"
                    }
                    backgroundGradient="from-transparent to-transparent"
                    enableParallax={true}
                >
                    <motion.button
                        initial={{ opacity: 0, scale: 0.5 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.8, delay: 1.5 }}
                        whileHover={{ scale: 1.05, y: -5 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={() => {
                            document
                                .getElementById("content")
                                ?.scrollIntoView({ behavior: "smooth" });
                        }}
                        className="bg-white/20 backdrop-blur-md text-white px-8 py-4 rounded-full text-lg font-semibold border border-white/30 hover:bg-white/30 transition-all duration-300"
                    >
                        Explore Our Village
                    </motion.button>
                </HeroSection>
            </section>

            {/* Featured Places Section */}
            {featuredPlaces && featuredPlaces.length > 0 && (
                <section
                    id="content"
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-5xl font-bold text-white mb-4">
                                Featured Places
                            </h2>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "10rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-green-400 to-blue-400 mx-auto"
                            />
                        </motion.div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {featuredPlaces.slice(0, 6).map((place, index) => (
                                <PlaceCard
                                    key={place.id}
                                    place={place}
                                    index={index}
                                    village={village}
                                />
                            ))}
                        </div>

                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ delay: 1, duration: 0.8 }}
                            className="text-center mt-12"
                        >
                            <Link href="/places">
                                <motion.button
                                    whileHover={{ scale: 1.05, y: -3 }}
                                    whileTap={{ scale: 0.95 }}
                                    className="px-8 py-4 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    Explore All Places →
                                </motion.button>
                            </Link>
                        </motion.div>
                    </div>
                </section>
            )}

            {/* Products Section */}
            {featuredProducts && featuredProducts.length > 0 && (
                <section
                    ref={productsRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-5xl font-bold text-white mb-4">
                                Local Products
                            </h2>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "10rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-indigo-400 to-blue-400 mx-auto"
                            />
                        </motion.div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {featuredProducts
                                .slice(0, 6)
                                .map((product, index) => (
                                    <ProductCard
                                        key={product.id}
                                        product={product}
                                        index={index}
                                        village={village}
                                    />
                                ))}
                        </div>

                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ delay: 1, duration: 0.8 }}
                            className="text-center mt-12"
                        >
                            <Link href="/products">
                                <motion.button
                                    whileHover={{ scale: 1.05, y: -3 }}
                                    whileTap={{ scale: 0.95 }}
                                    className="px-8 py-4 bg-gradient-to-r from-indigo-500 to-blue-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    View All Products →
                                </motion.button>
                            </Link>
                        </motion.div>
                    </div>
                </section>
            )}

            {/* Articles Section */}
            {featuredArticles && featuredArticles.length > 0 && (
                <section
                    ref={articlesRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-5xl font-bold text-white mb-4">
                                Village Stories
                            </h2>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "12rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-blue-400 to-purple-400 mx-auto"
                            />
                        </motion.div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {featuredArticles
                                .slice(0, 6)
                                .map((article, index) => (
                                    <ArticleCard
                                        key={article.id}
                                        article={article}
                                        index={index}
                                        village={village}
                                    />
                                ))}
                        </div>

                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ delay: 1, duration: 0.8 }}
                            className="text-center mt-12"
                        >
                            <Link href="/articles">
                                <motion.button
                                    whileHover={{ scale: 1.05, y: -3 }}
                                    whileTap={{ scale: 0.95 }}
                                    className="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    Read All Stories →
                                </motion.button>
                            </Link>
                        </motion.div>
                    </div>
                </section>
            )}

            {/* Gallery Section */}
            {featuredImages && featuredImages.length > 0 && (
                <section
                    ref={galleryRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-5xl font-bold text-white mb-4">
                                Village Gallery
                            </h2>
                            <motion.p
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                transition={{ delay: 0.3, duration: 0.8 }}
                                className="text-xl text-white/80 mb-6"
                            >
                                Capturing moments and memories from{" "}
                                {village?.name}
                            </motion.p>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "8rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-pink-400 to-purple-400 mx-auto"
                            />
                        </motion.div>

                        {/* Artistic Grid Layout */}
                        <div className="grid grid-cols-12 gap-4 max-w-7xl mx-auto">
                            {featuredImages.slice(0, 8).map((image, index) => {
                                const gridPatterns = [
                                    "col-span-6 row-span-2",
                                    "col-span-3 row-span-1",
                                    "col-span-3 row-span-1",
                                    "col-span-4 row-span-2",
                                    "col-span-4 row-span-1",
                                    "col-span-4 row-span-1",
                                    "col-span-6 row-span-1",
                                    "col-span-6 row-span-1",
                                ];

                                const aspectRatios = [
                                    "aspect-video",
                                    "aspect-square",
                                    "aspect-[4/3]",
                                    "aspect-[3/4]",
                                ];

                                return (
                                    <motion.div
                                        key={image.id}
                                        initial={{
                                            opacity: 0,
                                            scale: 0.6,
                                            rotateY: 45,
                                        }}
                                        whileInView={{
                                            opacity: 1,
                                            scale: 1,
                                            rotateY: 0,
                                        }}
                                        transition={{
                                            duration: 0.8,
                                            delay: (index % 6) * 0.1,
                                            type: "spring",
                                            stiffness: 100,
                                        }}
                                        whileHover={{
                                            scale: 1.05,
                                            rotateY: 8,
                                            z: 50,
                                        }}
                                        className={`${
                                            gridPatterns[
                                                index % gridPatterns.length
                                            ]
                                        } cursor-pointer group relative overflow-hidden rounded-2xl transform-gpu perspective-1000`}
                                    >
                                        <div
                                            className={`w-full ${
                                                aspectRatios[
                                                    index % aspectRatios.length
                                                ]
                                            } bg-gradient-to-br from-purple-500 to-pink-600 relative overflow-hidden`}
                                        >
                                            {image.image_url ? (
                                                <motion.img
                                                    src={image.image_url}
                                                    alt={
                                                        image.caption ||
                                                        "Village photo"
                                                    }
                                                    className="w-full h-full object-cover"
                                                    whileHover={{ scale: 1.1 }}
                                                    transition={{
                                                        duration: 0.5,
                                                    }}
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-white/50 relative">
                                                    <motion.span
                                                        className="text-4xl"
                                                        animate={{
                                                            rotate: [
                                                                0, 5, -5, 0,
                                                            ],
                                                            scale: [1, 1.1, 1],
                                                        }}
                                                        transition={{
                                                            duration: 3,
                                                            repeat: Infinity,
                                                            delay: index * 0.2,
                                                        }}
                                                    >
                                                        🖼️
                                                    </motion.span>
                                                </div>
                                            )}

                                            {/* Artistic overlay effects */}
                                            <motion.div
                                                initial={{ opacity: 0 }}
                                                whileHover={{ opacity: 1 }}
                                                className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"
                                            />

                                            {/* Image info overlay */}
                                            <motion.div
                                                initial={{
                                                    y: "100%",
                                                    opacity: 0,
                                                }}
                                                whileHover={{
                                                    y: 0,
                                                    opacity: 1,
                                                }}
                                                transition={{
                                                    duration: 0.4,
                                                    ease: "easeOut",
                                                }}
                                                className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4"
                                            >
                                                {image.place && (
                                                    <div className="text-xs text-purple-300 mb-1 flex items-center">
                                                        <span className="mr-1">
                                                            📍
                                                        </span>
                                                        {image.place.name}
                                                    </div>
                                                )}
                                                {image.caption && (
                                                    <div className="text-sm text-white font-medium line-clamp-2">
                                                        {image.caption}
                                                    </div>
                                                )}
                                            </motion.div>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </div>

                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            whileInView={{ opacity: 1, scale: 1 }}
                            transition={{ delay: 2, duration: 0.8 }}
                            className="text-center mt-12"
                        >
                            <Link href="/gallery">
                                <motion.button
                                    whileHover={{
                                        scale: 1.05,
                                        y: -3,
                                        boxShadow:
                                            "0 20px 40px rgba(255,255,255,0.1)",
                                    }}
                                    whileTap={{ scale: 0.95 }}
                                    className="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center mx-auto"
                                >
                                    <span className="mr-2">🖼️</span>
                                    View Full Gallery
                                    <motion.span
                                        animate={{ x: [0, 5, 0] }}
                                        transition={{
                                            duration: 1.5,
                                            repeat: Infinity,
                                        }}
                                        className="ml-2"
                                    >
                                        →
                                    </motion.span>
                                </motion.button>
                            </Link>
                        </motion.div>
                    </div>
                </section>
            )}
        </MainLayout>
    );
};

export default Home;
