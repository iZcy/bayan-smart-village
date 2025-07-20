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
import MediaBackground from "@/Components/MediaBackground";
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
    const [selectedSMEPlace, setSelectedSMEPlace] = useState(0);
    const [isUserInteracting, setIsUserInteracting] = useState(false);
    const [interactionTimeout, setInteractionTimeout] = useState(null);
    const [mediaLoaded, setMediaLoaded] = useState(false);

    const { scrollY } = useScroll();

    // Separate places by type - Tourism (services) vs SME (products)
    const tourismPlaces =
        featuredPlaces?.filter((place) => place.category?.type === "service") ||
        [];

    const smePlaces =
        featuredPlaces?.filter((place) => place.category?.type === "product") ||
        [];

    // Handle media loading
    const handleMediaLoad = ({ type, loaded, error }) => {
        if (loaded && !error) {
            setMediaLoaded(true);
        }
    };

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

    // Auto-scroll for tourism places (services)
    useEffect(() => {
        if (isUserInteracting || tourismPlaces.length === 0) return;
        const interval = setInterval(() => {
            setSelectedTourismPlace(
                (prev) => (prev + 1) % tourismPlaces.length
            );
        }, 5000);
        return () => clearInterval(interval);
    }, [tourismPlaces.length, isUserInteracting]);

    // Auto-scroll for SME places (product businesses)
    useEffect(() => {
        if (isUserInteracting || smePlaces.length === 0) return;
        const interval = setInterval(() => {
            setSelectedSMEPlace((prev) => (prev + 1) % smePlaces.length);
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

    return (
        <MainLayout title={`Welcome to ${village?.name}`}>
            <Head title={`${village?.name} - Smart Village`} />

            {/* Dynamic Media Background */}
            <MediaBackground
                context="home"
                village={village}
                enableControls={true}
                onMediaLoad={handleMediaLoad}
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Color Overlay */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{ background: colorOverlay }}
            />

            {/* Hero Section */}
            <section
                ref={heroRef}
                className="relative h-screen overflow-hidden z-10"
                onClick={handleUserInteraction}
                onMouseMove={handleUserInteraction}
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
                                .getElementById("tourism")
                                ?.scrollIntoView({ behavior: "smooth" });
                        }}
                        className="bg-white/20 backdrop-blur-md text-white px-8 py-4 rounded-full text-lg font-semibold border border-white/30 hover:bg-white/30 transition-all duration-300"
                    >
                        Explore Our Village
                    </motion.button>
                </HeroSection>
            </section>

            {/* Tourism Services Section */}
            {tourismPlaces && tourismPlaces.length > 0 && (
                <section
                    id="tourism"
                    ref={tourismRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                    onClick={handleUserInteraction}
                    onMouseEnter={handleUserInteraction}
                    onTouchStart={handleUserInteraction}
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 h-full relative z-10">
                        <motion.h2
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-5xl font-bold text-white text-center mb-16"
                        >
                            🏞️ Tourism & Services
                        </motion.h2>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                            {/* Interactive Service Map - Left */}
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-6 flex items-center justify-center"
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={selectedTourismPlace}
                                        initial={{ scale: 0.8, opacity: 0 }}
                                        animate={{ scale: 1, opacity: 1 }}
                                        exit={{ scale: 0.8, opacity: 0 }}
                                        transition={{ duration: 0.5 }}
                                        className="w-full h-80 bg-gradient-to-br from-green-400/30 to-blue-400/30 rounded-xl flex items-center justify-center relative overflow-hidden"
                                    >
                                        {/* Display place image if available */}
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.image_url ? (
                                            <img
                                                src={
                                                    tourismPlaces[
                                                        selectedTourismPlace
                                                    ].image_url
                                                }
                                                alt={
                                                    tourismPlaces[
                                                        selectedTourismPlace
                                                    ].name
                                                }
                                                className="w-full h-full object-cover rounded-xl"
                                            />
                                        ) : (
                                            <>
                                                {/* Animated service elements */}
                                                <motion.div
                                                    animate={{ rotate: 360 }}
                                                    transition={{
                                                        duration: 20,
                                                        repeat: Infinity,
                                                        ease: "linear",
                                                    }}
                                                    className="absolute top-4 right-4 w-8 h-8 border-2 border-white/50 rounded-full"
                                                />

                                                <div className="text-center text-white">
                                                    <motion.div
                                                        animate={{
                                                            y: [0, -10, 0],
                                                        }}
                                                        transition={{
                                                            duration: 2,
                                                            repeat: Infinity,
                                                        }}
                                                        className="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4"
                                                    >
                                                        🏨
                                                    </motion.div>
                                                    <h3 className="text-xl font-semibold mb-2">
                                                        {tourismPlaces[
                                                            selectedTourismPlace
                                                        ]?.name ||
                                                            "Tourism Service"}
                                                    </h3>
                                                    <p className="text-sm opacity-75">
                                                        Interactive service view
                                                    </p>
                                                </div>
                                            </>
                                        )}

                                        {/* Overlay with place info */}
                                        <div className="absolute inset-0 bg-black/40 flex items-end">
                                            <div className="p-4 text-white w-full">
                                                <h3 className="text-xl font-semibold mb-1">
                                                    {
                                                        tourismPlaces[
                                                            selectedTourismPlace
                                                        ]?.name
                                                    }
                                                </h3>
                                                <p className="text-sm opacity-75">
                                                    {tourismPlaces[
                                                        selectedTourismPlace
                                                    ]?.category?.name ||
                                                        "Tourism Service"}
                                                </p>
                                            </div>
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Service Description - Center */}
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
                                                ?.name || "Tourism Service"}
                                        </h3>
                                        <p className="text-lg opacity-90 mb-6 leading-relaxed">
                                            {tourismPlaces[selectedTourismPlace]
                                                ?.description ||
                                                "Experience amazing services and hospitality in our beautiful village."}
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
                                            {tourismPlaces[selectedTourismPlace]
                                                ?.latitude &&
                                                tourismPlaces[
                                                    selectedTourismPlace
                                                ]?.longitude && (
                                                    <div className="flex items-center">
                                                        <span className="text-green-200">
                                                            🗺️
                                                        </span>
                                                        <span className="ml-2">
                                                            <a
                                                                href={`https://www.google.com/maps/search/?api=1&query=${tourismPlaces[selectedTourismPlace].latitude},${tourismPlaces[selectedTourismPlace].longitude}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="hover:text-green-300 underline"
                                                            >
                                                                View on Map
                                                            </a>
                                                        </span>
                                                    </div>
                                                )}
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Services List - Right */}
                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20"
                            >
                                {tourismPlaces
                                    .slice(0, 6)
                                    .map((place, index) => (
                                        <motion.div
                                            key={place.id}
                                            onClick={() => {
                                                setSelectedTourismPlace(index);
                                                handleUserInteraction();
                                            }}
                                            layoutId={`tourism-${place.id}`}
                                            whileHover={{ scale: 1.02, x: 10 }}
                                            whileTap={{ scale: 0.98 }}
                                            className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                                selectedTourismPlace === index
                                                    ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                                    : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                            }`}
                                        >
                                            <div className="flex items-center space-x-4">
                                                <motion.div
                                                    className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden"
                                                    whileHover={{ rotate: 5 }}
                                                >
                                                    {place.image_url ? (
                                                        <img
                                                            src={
                                                                place.image_url
                                                            }
                                                            alt={place.name}
                                                            className="w-full h-full object-cover rounded-lg"
                                                        />
                                                    ) : (
                                                        <span className="text-2xl">
                                                            {place.category?.name?.includes(
                                                                "Hotel"
                                                            )
                                                                ? "🏨"
                                                                : place.category?.name?.includes(
                                                                      "Restaurant"
                                                                  )
                                                                ? "🍽️"
                                                                : place.category?.name?.includes(
                                                                      "Tour"
                                                                  )
                                                                ? "🎯"
                                                                : "🏞️"}
                                                        </span>
                                                    )}
                                                    {selectedTourismPlace ===
                                                        index && (
                                                        <motion.div
                                                            layoutId="tourism-selection-indicator"
                                                            className="absolute inset-0 border-2 border-white rounded-lg"
                                                        />
                                                    )}
                                                </motion.div>

                                                <div className="flex-1 text-white">
                                                    <h4 className="font-semibold text-lg">
                                                        {place.name}
                                                    </h4>
                                                    <p className="text-sm opacity-75 line-clamp-2">
                                                        {place.category?.name ||
                                                            "Tourism Service"}
                                                    </p>
                                                </div>

                                                <motion.div
                                                    animate={{
                                                        scale:
                                                            selectedTourismPlace ===
                                                            index
                                                                ? 1.2
                                                                : 1,
                                                        opacity:
                                                            selectedTourismPlace ===
                                                            index
                                                                ? 1
                                                                : 0.3,
                                                    }}
                                                    className="w-3 h-3 rounded-full bg-white"
                                                />
                                            </div>
                                        </motion.div>
                                    ))}
                            </motion.div>
                        </div>
                    </div>
                </section>
            )}

            {/* SME Business Places Section */}
            {smePlaces && smePlaces.length > 0 && (
                <section
                    ref={smeRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                    onClick={handleUserInteraction}
                    onMouseEnter={handleUserInteraction}
                    onTouchStart={handleUserInteraction}
                >
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 h-full relative z-10">
                        <motion.h2
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-5xl font-bold text-white text-center mb-16"
                        >
                            🏪 Local Product Businesses
                        </motion.h2>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                            {/* Business Places List - LEFT */}
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8 }}
                                className="space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20"
                            >
                                {smePlaces.slice(0, 6).map((place, index) => (
                                    <motion.div
                                        key={place.id}
                                        onClick={() => {
                                            setSelectedSMEPlace(index);
                                            handleUserInteraction();
                                        }}
                                        layoutId={`sme-${place.id}`}
                                        whileHover={{ scale: 1.02, x: -10 }}
                                        whileTap={{ scale: 0.98 }}
                                        className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                            selectedSMEPlace === index
                                                ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                                : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                        }`}
                                    >
                                        <div className="flex items-center space-x-4">
                                            <motion.div
                                                animate={{
                                                    scale:
                                                        selectedSMEPlace ===
                                                        index
                                                            ? 1.2
                                                            : 1,
                                                    opacity:
                                                        selectedSMEPlace ===
                                                        index
                                                            ? 1
                                                            : 0.3,
                                                }}
                                                className="w-3 h-3 rounded-full bg-white"
                                            />

                                            <div className="flex-1 text-white">
                                                <h4 className="font-semibold text-lg">
                                                    {place.name}
                                                </h4>
                                                <p className="text-sm opacity-75 line-clamp-2">
                                                    {place.category?.name ||
                                                        "Product Business"}
                                                </p>
                                            </div>

                                            <motion.div
                                                className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden"
                                                whileHover={{ rotate: -5 }}
                                            >
                                                {place.image_url ? (
                                                    <img
                                                        src={place.image_url}
                                                        alt={place.name}
                                                        className="w-full h-full object-cover rounded-lg"
                                                    />
                                                ) : (
                                                    <span className="text-2xl">
                                                        {place.category?.name?.includes(
                                                            "Craft"
                                                        )
                                                            ? "🎨"
                                                            : place.category?.name?.includes(
                                                                  "Market"
                                                              )
                                                            ? "🏬"
                                                            : place.category?.name?.includes(
                                                                  "Shop"
                                                              )
                                                            ? "🛒"
                                                            : "🏪"}
                                                    </span>
                                                )}
                                                {selectedSMEPlace === index && (
                                                    <motion.div
                                                        layoutId="sme-selection-indicator"
                                                        className="absolute inset-0 border-2 border-white rounded-lg"
                                                    />
                                                )}
                                            </motion.div>
                                        </div>
                                    </motion.div>
                                ))}
                            </motion.div>

                            {/* Business Description - CENTER */}
                            <motion.div
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8, delay: 0.2 }}
                                className="flex flex-col justify-center"
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={selectedSMEPlace}
                                        initial={{
                                            opacity: 0,
                                            x: -20,
                                            rotateY: 45,
                                        }}
                                        animate={{
                                            opacity: 1,
                                            x: 0,
                                            rotateY: 0,
                                        }}
                                        exit={{
                                            opacity: 0,
                                            x: 20,
                                            rotateY: -45,
                                        }}
                                        transition={{
                                            duration: 0.6,
                                            type: "spring",
                                        }}
                                        className="text-white bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10"
                                    >
                                        <motion.h3
                                            className="text-3xl font-bold mb-4"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.2 }}
                                        >
                                            {smePlaces[selectedSMEPlace]
                                                ?.name || "Local Business"}
                                        </motion.h3>

                                        <motion.p
                                            className="text-lg opacity-90 mb-6 leading-relaxed"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.3 }}
                                        >
                                            {smePlaces[selectedSMEPlace]
                                                ?.description ||
                                                "Supporting local economy through quality products and traditional craftsmanship."}
                                        </motion.p>

                                        <motion.div
                                            className="space-y-3"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.4 }}
                                        >
                                            <div className="flex items-center p-3 bg-orange-500/20 rounded-lg">
                                                <span className="text-orange-200 text-xl mr-3">
                                                    📍
                                                </span>
                                                <span className="text-sm">
                                                    {smePlaces[selectedSMEPlace]
                                                        ?.address ||
                                                        "Village Location"}
                                                </span>
                                            </div>

                                            {smePlaces[selectedSMEPlace]
                                                ?.phone_number && (
                                                <div className="flex items-center p-3 bg-orange-500/20 rounded-lg">
                                                    <span className="text-orange-200 text-xl mr-3">
                                                        📞
                                                    </span>
                                                    <span className="text-sm">
                                                        {
                                                            smePlaces[
                                                                selectedSMEPlace
                                                            ].phone_number
                                                        }
                                                    </span>
                                                </div>
                                            )}

                                            {smePlaces[selectedSMEPlace]
                                                ?.latitude &&
                                                smePlaces[selectedSMEPlace]
                                                    ?.longitude && (
                                                    <div className="flex items-center p-3 bg-orange-500/20 rounded-lg">
                                                        <span className="text-orange-200 text-xl mr-3">
                                                            🗺️
                                                        </span>
                                                        <a
                                                            href={`https://www.google.com/maps/search/?api=1&query=${smePlaces[selectedSMEPlace].latitude},${smePlaces[selectedSMEPlace].longitude}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-sm hover:text-orange-300 underline"
                                                        >
                                                            View on Map
                                                        </a>
                                                    </div>
                                                )}

                                            <motion.div
                                                whileHover={{
                                                    scale: 1.05,
                                                    y: -2,
                                                }}
                                                whileTap={{ scale: 0.95 }}
                                                className="w-full mt-4"
                                            >
                                                <Link
                                                    href={`/places/${smePlaces[selectedSMEPlace]?.slug}`}
                                                    className="block w-full px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-300 text-center"
                                                >
                                                    Visit Business →
                                                </Link>
                                            </motion.div>
                                        </motion.div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Interactive Business Map - RIGHT */}
                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-6 flex items-center justify-center"
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={selectedSMEPlace}
                                        initial={{
                                            scale: 0.8,
                                            opacity: 0,
                                            rotateY: -45,
                                        }}
                                        animate={{
                                            scale: 1,
                                            opacity: 1,
                                            rotateY: 0,
                                        }}
                                        exit={{
                                            scale: 0.8,
                                            opacity: 0,
                                            rotateY: 45,
                                        }}
                                        transition={{
                                            duration: 0.6,
                                            type: "spring",
                                        }}
                                        className="w-full h-80 bg-gradient-to-br from-orange-400/30 to-red-400/30 rounded-xl flex items-center justify-center relative overflow-hidden"
                                    >
                                        {/* Display place image if available */}
                                        {smePlaces[selectedSMEPlace]
                                            ?.image_url ? (
                                            <img
                                                src={
                                                    smePlaces[selectedSMEPlace]
                                                        .image_url
                                                }
                                                alt={
                                                    smePlaces[selectedSMEPlace]
                                                        .name
                                                }
                                                className="w-full h-full object-cover rounded-xl"
                                            />
                                        ) : (
                                            <>
                                                {/* Animated business elements */}
                                                <motion.div
                                                    animate={{
                                                        rotate: [0, 10, -10, 0],
                                                        scale: [1, 1.1, 1],
                                                    }}
                                                    transition={{
                                                        duration: 4,
                                                        repeat: Infinity,
                                                    }}
                                                    className="absolute top-6 left-6 w-6 h-6 bg-white/30 rounded-sm"
                                                />

                                                <motion.div
                                                    animate={{
                                                        y: [0, -20, 0],
                                                        opacity: [0.5, 1, 0.5],
                                                    }}
                                                    transition={{
                                                        duration: 3,
                                                        repeat: Infinity,
                                                        delay: 1,
                                                    }}
                                                    className="absolute bottom-6 right-6 w-8 h-8 border-2 border-white/40 rounded-full"
                                                />

                                                <div className="text-center text-white">
                                                    <motion.div
                                                        animate={{
                                                            rotateY: [0, 360],
                                                            scale: [1, 1.2, 1],
                                                        }}
                                                        transition={{
                                                            duration: 3,
                                                            repeat: Infinity,
                                                        }}
                                                        className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center mx-auto mb-4"
                                                    >
                                                        🏪
                                                    </motion.div>
                                                    <h3 className="text-xl font-semibold mb-2">
                                                        {smePlaces[
                                                            selectedSMEPlace
                                                        ]?.name ||
                                                            "Business Location"}
                                                    </h3>
                                                    <p className="text-sm opacity-75">
                                                        Interactive business
                                                        view
                                                    </p>
                                                </div>
                                            </>
                                        )}

                                        {/* Overlay with place info */}
                                        <div className="absolute inset-0 bg-black/40 flex items-end">
                                            <div className="p-4 text-white w-full">
                                                <h3 className="text-xl font-semibold mb-1">
                                                    {
                                                        smePlaces[
                                                            selectedSMEPlace
                                                        ]?.name
                                                    }
                                                </h3>
                                                <p className="text-sm opacity-75">
                                                    {smePlaces[selectedSMEPlace]
                                                        ?.category?.name ||
                                                        "Product Business"}
                                                </p>
                                            </div>
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>
                        </div>
                    </div>
                </section>
            )}

            {/* Featured Products Section */}
            {featuredProducts && featuredProducts.length > 0 && (
                <section
                    ref={productsRef}
                    className="min-h-screen relative overflow-hidden py-20 z-10"
                    onClick={handleUserInteraction}
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
                                🛍️ Featured Products
                            </h2>
                            <motion.p
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                transition={{ delay: 0.3, duration: 0.8 }}
                                className="text-xl text-white/80 mb-6"
                            >
                                Authentic products made by local artisans and
                                businesses
                            </motion.p>
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
                    onClick={handleUserInteraction}
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
                                📖 Village Stories
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
                    onClick={handleUserInteraction}
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
                                📸 Village Gallery
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

            {/* Media Loading Status */}
            {!mediaLoaded && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    className="fixed bottom-4 left-4 z-50 bg-blue-500/80 text-white px-3 py-2 rounded text-sm"
                >
                    Loading media content...
                </motion.div>
            )}
        </MainLayout>
    );
};

export default Home;
