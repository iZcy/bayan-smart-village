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

// Lightbox Modal Component
const LightboxModal = ({
    image,
    onClose,
    onNavigate,
    currentIndex,
    totalImages,
}) => {
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === "Escape") onClose();
            if (e.key === "ArrowLeft") onNavigate("prev");
            if (e.key === "ArrowRight") onNavigate("next");
        };

        document.addEventListener("keydown", handleKeyDown);
        document.body.style.overflow = "hidden";

        return () => {
            document.removeEventListener("keydown", handleKeyDown);
            document.body.style.overflow = "unset";
        };
    }, [onClose, onNavigate]);

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/95 backdrop-blur-md z-50 flex items-center justify-center p-4"
            onClick={onClose}
        >
            <div className="relative max-w-7xl max-h-full">
                {/* Close Button */}
                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black/50 rounded-full"
                >
                    <svg
                        className="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                {/* Navigation Buttons */}
                {totalImages > 1 && (
                    <>
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                onNavigate("prev");
                            }}
                            className="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black/50 rounded-full"
                        >
                            <svg
                                className="w-6 h-6"
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
                        </button>

                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                onNavigate("next");
                            }}
                            className="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black/50 rounded-full"
                        >
                            <svg
                                className="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </>
                )}

                {/* Image */}
                <motion.div
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ duration: 0.3 }}
                    className="relative"
                    onClick={(e) => e.stopPropagation()}
                >
                    <img
                        src={image.image_url}
                        alt={image.caption || "Gallery image"}
                        className="max-w-full max-h-[80vh] object-contain mx-auto rounded-lg shadow-2xl"
                    />

                    {/* Image Info */}
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 rounded-b-lg">
                        <div className="flex items-center justify-between text-white">
                            <div>
                                {image.place && (
                                    <div className="text-sm text-purple-300 mb-2">
                                        📍 {image.place.name}
                                    </div>
                                )}
                                {image.caption && (
                                    <div className="text-lg font-semibold mb-1">
                                        {image.caption}
                                    </div>
                                )}
                                <div className="text-sm text-gray-400">
                                    {new Date(
                                        image.created_at
                                    ).toLocaleDateString("en-US", {
                                        year: "numeric",
                                        month: "long",
                                        day: "numeric",
                                    })}
                                </div>
                            </div>
                            <div className="text-sm text-gray-400">
                                {currentIndex + 1} / {totalImages}
                            </div>
                        </div>
                    </div>
                </motion.div>
            </div>
        </motion.div>
    );
};

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
    const [isTourismInteracting, setIsTourismInteracting] = useState(false);
    const [isSMEInteracting, setIsSMEInteracting] = useState(false);
    const [interactionTimeout, setInteractionTimeout] = useState(null);
    const [mediaLoaded, setMediaLoaded] = useState(false);

    // Gallery lightbox state
    const [selectedImage, setSelectedImage] = useState(null);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const { scrollY } = useScroll();

    // Gallery lightbox functions
    const openLightbox = (image) => {
        setSelectedImage(image);
        setIsLightboxOpen(true);
    };

    const closeLightbox = () => {
        setSelectedImage(null);
        setIsLightboxOpen(false);
    };

    const navigateImage = (direction) => {
        if (!featuredImages || featuredImages.length === 0) return;

        const currentIndex = featuredImages.findIndex(
            (img) => img.id === selectedImage.id
        );
        let newIndex;

        if (direction === "prev") {
            newIndex =
                currentIndex === 0
                    ? featuredImages.length - 1
                    : currentIndex - 1;
        } else {
            newIndex =
                currentIndex === featuredImages.length - 1
                    ? 0
                    : currentIndex + 1;
        }

        setSelectedImage(featuredImages[newIndex]);
    };

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

        // Clear existing timeout
        if (interactionTimeout) {
            clearTimeout(interactionTimeout);
        }

        // Set new timeout to reset interaction state after 5 seconds of inactivity
        const timeout = setTimeout(() => {
            setIsUserInteracting(false);
        }, 5000);

        setInteractionTimeout(timeout);
    };

    // Add event listeners for various user interactions
    useEffect(() => {
        const events = [
            "mousedown",
            "mousemove",
            "wheel",
            "scroll",
            "touchstart",
            "touchmove",
            "keydown",
        ];

        events.forEach((event) => {
            document.addEventListener(event, handleUserInteraction, {
                passive: true,
            });
        });

        return () => {
            events.forEach((event) => {
                document.removeEventListener(event, handleUserInteraction);
            });
            if (interactionTimeout) {
                clearTimeout(interactionTimeout);
            }
        };
    }, [interactionTimeout]);

    // Auto-scroll for tourism places (services) - Updated with interaction checks
    useEffect(() => {
        if (
            isUserInteracting ||
            isTourismInteracting ||
            tourismPlaces.length === 0
        ) {
            return; // Don't auto-scroll if user is interacting
        }

        const interval = setInterval(() => {
            setSelectedTourismPlace(
                (prev) => (prev + 1) % tourismPlaces.length
            );
        }, 5000);
        return () => clearInterval(interval);
    }, [tourismPlaces.length, isUserInteracting, isTourismInteracting]);

    // Auto-scroll for SME places (product businesses) - Updated with interaction checks
    useEffect(() => {
        if (isUserInteracting || isSMEInteracting || smePlaces.length === 0) {
            return; // Don't auto-scroll if user is interacting
        }

        const interval = setInterval(() => {
            setSelectedSMEPlace((prev) => (prev + 1) % smePlaces.length);
        }, 5000);
        return () => clearInterval(interval);
    }, [smePlaces.length, isUserInteracting, isSMEInteracting]);

    // Section refs
    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [tourismRef, tourismInView] = useInView({ threshold: 0.3 });
    const [smeRef, smeInView] = useInView({ threshold: 0.3 });
    const [productsRef, productsInView] = useInView({ threshold: 0.3 });
    const [articlesRef, articlesInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({ threshold: 0.1 });

    // Color overlay based on scroll - Enhanced from previous version
    const colorOverlay = useTransform(
        scrollY,
        [0, 800, 1600, 2400, 3200, 4000, 4800],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.2))", // Hero
            "linear-gradient(to bottom, rgba(34,197,94,0.4), rgba(34,197,94,0.6))", // Tourism - green
            "linear-gradient(to bottom, rgba(245,158,11,0.4), rgba(234,88,12,0.6))", // SME - amber to orange
            "linear-gradient(to bottom, rgba(79,70,229,0.4), rgba(29,78,216,0.6))", // Products - indigo to blue
            "linear-gradient(to bottom, rgba(37,99,235,0.4), rgba(126,34,206,0.6))", // Articles - blue to purple
            "linear-gradient(to bottom, rgba(126,34,206,0.4), rgba(219,39,119,0.6))", // Gallery - purple to pink
            "linear-gradient(to bottom, rgba(219,39,119,0.3), rgba(0,0,0,0.4))", // End fade
        ]
    );

    return (
        <MainLayout title={`Welcome to ${village?.name}`}>
            <Head title={`${village?.name} - Smart Village`} />

            {/* Audio Controls - MediaBackground for Audio Only */}
            <MediaBackground
                context="home"
                village={village}
                enableControls={true}
                audioOnly={true}
                controlsId="home-media-controls"
                onMediaLoad={handleMediaLoad}
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Video Background - Separate Fixed Background */}
            <div className="fixed inset-0 bg-cover bg-center z-0">
                <video
                    className="w-full h-full object-cover"
                    autoPlay
                    muted
                    loop
                    playsInline
                >
                    <source src="/video/videobackground.mp4" type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
                <div className="absolute inset-0 bg-black/30" />
            </div>

            {/* Enhanced Color Overlay for sections */}
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
                    className="min-h-fit lg:min-h-screen relative overflow-x-hidden py-20 z-10"
                    onMouseEnter={() => {
                        setIsTourismInteracting(true);
                        handleUserInteraction();
                    }}
                    onMouseLeave={() => setIsTourismInteracting(false)}
                    onTouchStart={handleUserInteraction}
                    onWheel={handleUserInteraction}
                >
                    {/* Enhanced backdrop blur from previous version */}
                    <div className="absolute inset-0 backdrop-blur-sm" />

                    <div className="container mx-auto px-4 sm:px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-12 lg:mb-16"
                        >
                            <h2 className="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold text-white mb-4">
                                🏞️ Tourism & Services
                            </h2>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "10rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-indigo-400 to-blue-400 mx-auto"
                            />
                        </motion.div>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                            {/* Real Google Maps - Left (Restored from previous version) */}
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-3 sm:p-4 md:p-6 order-2 lg:order-1 flex items-center justify-center min-h-80"
                                onMouseDown={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onWheel={handleUserInteraction}
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={selectedTourismPlace}
                                        initial={{ scale: 0.8, opacity: 0 }}
                                        animate={{
                                            scale: 1,
                                            opacity: 1,
                                            transition:
                                                isTourismInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : { duration: 0.5 },
                                        }}
                                        exit={{
                                            scale: 0.8,
                                            opacity: 0,
                                            transition:
                                                isTourismInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : { duration: 0.5 },
                                        }}
                                        className="w-full h-60 sm:h-72 lg:h-80 bg-gradient-to-br from-green-400/30 to-blue-400/30 rounded-xl relative overflow-hidden"
                                        onMouseDown={handleUserInteraction}
                                        onTouchStart={handleUserInteraction}
                                        onWheel={handleUserInteraction}
                                    >
                                        {/* Real Google Maps embed - ACTIVE */}
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.latitude &&
                                        tourismPlaces[selectedTourismPlace]
                                            ?.longitude ? (
                                            <div className="w-full h-full relative">
                                                <iframe
                                                    src={`https://maps.google.com/maps?q=${tourismPlaces[selectedTourismPlace].latitude},${tourismPlaces[selectedTourismPlace].longitude}&z=15&output=embed`}
                                                    width="100%"
                                                    height="100%"
                                                    style={{ border: 0 }}
                                                    className="rounded-xl"
                                                    onLoad={(e) => {
                                                        try {
                                                            const iframe =
                                                                e.target;
                                                            iframe.onmousedown =
                                                                handleUserInteraction;
                                                            iframe.ontouchstart =
                                                                handleUserInteraction;
                                                        } catch (error) {
                                                            console.log(
                                                                "Cannot add iframe interaction listeners due to CORS"
                                                            );
                                                        }
                                                    }}
                                                />

                                                {/* Overlay with place info */}
                                                <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 rounded-b-xl">
                                                    <h3 className="text-white font-semibold text-lg">
                                                        {tourismPlaces[
                                                            selectedTourismPlace
                                                        ]?.name ||
                                                            "Beautiful Destination"}
                                                    </h3>
                                                    <p className="text-white/80 text-sm">
                                                        📍{" "}
                                                        {tourismPlaces[
                                                            selectedTourismPlace
                                                        ]?.address ||
                                                            "Village Location"}
                                                    </p>
                                                    <a
                                                        href={`https://www.google.com/maps/search/?api=1&query=${tourismPlaces[selectedTourismPlace].latitude},${tourismPlaces[selectedTourismPlace].longitude}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-xs hover:bg-white/30 transition-colors"
                                                        onClick={
                                                            handleUserInteraction
                                                        }
                                                    >
                                                        Open in Maps →
                                                    </a>
                                                </div>
                                            </div>
                                        ) : (
                                            // Fallback for places without coordinates
                                            <div className="w-full h-full flex items-center justify-center">
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
                                                        📍
                                                    </motion.div>
                                                    <h3 className="text-xl font-semibold mb-2 line-clamp-1">
                                                        {tourismPlaces[
                                                            selectedTourismPlace
                                                        ]?.name ||
                                                            "Beautiful Destination"}
                                                    </h3>
                                                    <p className="text-sm opacity-75">
                                                        Interactive location
                                                        view
                                                    </p>
                                                </div>
                                            </div>
                                        )}
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Service Description - Center */}
                            <motion.div
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8, delay: 0.2 }}
                                className="order-1 lg:order-2 flex flex-col justify-center min-h-80"
                                onScroll={handleUserInteraction}
                                onWheel={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onTouchMove={handleUserInteraction}
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={selectedTourismPlace}
                                        initial={{ opacity: 0, x: 20 }}
                                        animate={{
                                            opacity: 1,
                                            x: 0,
                                            transition:
                                                isTourismInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : { duration: 0.5 },
                                        }}
                                        exit={{
                                            opacity: 0,
                                            x: -20,
                                            transition:
                                                isTourismInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : { duration: 0.5 },
                                        }}
                                        className="text-white bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 lg:p-8 border border-white/10"
                                        onMouseDown={handleUserInteraction}
                                        onTouchStart={handleUserInteraction}
                                    >
                                        <h3 className="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold mb-4">
                                            {tourismPlaces[selectedTourismPlace]
                                                ?.name ||
                                                "Beautiful Destination"}
                                        </h3>
                                        <motion.div
                                            className="text-sm sm:text-base lg:text-lg opacity-90 mb-4 sm:mb-6 leading-relaxed"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.3 }}
                                        >
                                            <p className="line-clamp-3">
                                                {tourismPlaces[
                                                    selectedTourismPlace
                                                ]?.description
                                                    ? tourismPlaces[
                                                          selectedTourismPlace
                                                      ].description
                                                    : "Explore the natural beauty and cultural richness of this amazing destination."}
                                            </p>
                                        </motion.div>
                                        <div className="space-y-2">
                                            <div className="flex items-center">
                                                <span className="text-green-200">
                                                    📍
                                                </span>
                                                <span className="ml-2 text-sm">
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
                                                    <span className="ml-2 text-sm">
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
                                                        <span className="ml-2 text-sm">
                                                            {
                                                                tourismPlaces[
                                                                    selectedTourismPlace
                                                                ].latitude
                                                            }
                                                            ,{" "}
                                                            {
                                                                tourismPlaces[
                                                                    selectedTourismPlace
                                                                ].longitude
                                                            }
                                                        </span>
                                                    </div>
                                                )}

                                            <motion.button
                                                whileHover={{
                                                    scale: 1.05,
                                                    y: -2,
                                                }}
                                                whileTap={{ scale: 0.95 }}
                                                className="w-full mt-4 px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-300"
                                                onClick={handleUserInteraction}
                                            >
                                                Visit Place →
                                            </motion.button>
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Enhanced Places List - Right (Restored from previous version) */}
                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="space-y-3 sm:space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20 pr-1 sm:pr-2 order-3 lg:order-3"
                                onScroll={handleUserInteraction}
                                onWheel={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onTouchMove={handleUserInteraction}
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
                                            whileHover={{
                                                scale: 1.02,
                                                boxShadow:
                                                    "0 10px 30px rgba(255,255,255,0.1)",
                                            }}
                                            whileTap={{ scale: 0.98 }}
                                            className={`p-3 sm:p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                                selectedTourismPlace === index
                                                    ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                                    : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                            }`}
                                            style={{ overflow: "visible" }}
                                            onMouseDown={handleUserInteraction}
                                            onTouchStart={handleUserInteraction}
                                        >
                                            <div className="flex items-center space-x-3 sm:space-x-4">
                                                <motion.div
                                                    className="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
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
                                                        <span className="text-xl sm:text-2xl">
                                                            🏞️
                                                        </span>
                                                    )}
                                                    {selectedTourismPlace ===
                                                        index && (
                                                        <motion.div
                                                            layoutId="selection-indicator"
                                                            className="absolute inset-0 border-2 border-white rounded-lg"
                                                        />
                                                    )}
                                                </motion.div>

                                                <div className="flex-1 text-white min-w-0">
                                                    <h4 className="font-semibold text-sm sm:text-base lg:text-lg truncate">
                                                        {place.name}
                                                    </h4>
                                                    <p className="text-xs sm:text-sm opacity-75 truncate">
                                                        {place.category?.name}
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
                                                    className="w-3 h-3 rounded-full bg-white flex-shrink-0"
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
                    id="sme"
                    ref={smeRef}
                    className="min-h-fit lg:min-h-screen relative overflow-x-hidden py-20 z-10"
                    onMouseEnter={() => {
                        setIsSMEInteracting(true);
                        handleUserInteraction();
                    }}
                    onMouseLeave={() => setIsSMEInteracting(false)}
                    onTouchStart={handleUserInteraction}
                    onWheel={handleUserInteraction}
                >
                    {/* Enhanced backdrop blur from previous version */}
                    <div className="absolute inset-0 backdrop-blur-sm" />

                    <div className="container mx-auto px-4 sm:px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-12 lg:mb-16"
                        >
                            <h2 className="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold text-white mb-4">
                                🏪 Local Product Businesses
                            </h2>
                            <motion.div
                                initial={{ width: 0 }}
                                whileInView={{ width: "10rem" }}
                                transition={{ delay: 0.5, duration: 1 }}
                                className="h-1 bg-gradient-to-r from-indigo-400 to-blue-400 mx-auto"
                            />
                        </motion.div>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                            {/* Enhanced SME Places List - LEFT (From previous version) */}
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8 }}
                                className="space-y-3 sm:space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20 pr-1 sm:pr-2 order-3 lg:order-1"
                                onScroll={handleUserInteraction}
                                onWheel={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onTouchMove={handleUserInteraction}
                            >
                                {smePlaces.slice(0, 6).map((place, index) => (
                                    <motion.div
                                        key={place.id}
                                        onClick={() => {
                                            setSelectedSMEPlace(index);
                                            handleUserInteraction();
                                        }}
                                        layoutId={`sme-${place.id}`}
                                        whileHover={{
                                            scale: 1.02,
                                            boxShadow:
                                                "0 10px 30px rgba(255,255,255,0.1)",
                                        }}
                                        whileTap={{ scale: 0.98 }}
                                        className={`p-3 sm:p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                            selectedSMEPlace === index
                                                ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                                : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                        }`}
                                        style={{ overflow: "visible" }}
                                        onMouseDown={handleUserInteraction}
                                        onTouchStart={handleUserInteraction}
                                    >
                                        <div className="flex items-center space-x-3 sm:space-x-4">
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
                                                className="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-white flex-shrink-0"
                                            />

                                            <div className="flex-1 text-white min-w-0">
                                                <h4 className="font-semibold text-sm sm:text-base lg:text-lg truncate">
                                                    {place.name}
                                                </h4>
                                                <p className="text-xs sm:text-sm opacity-75 truncate">
                                                    {place.category?.name}
                                                </p>
                                            </div>

                                            <motion.div
                                                className="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
                                                whileHover={{ rotate: -5 }}
                                            >
                                                {place.image_url ? (
                                                    <img
                                                        src={place.image_url}
                                                        alt={place.name}
                                                        className="w-full h-full object-cover rounded-lg"
                                                    />
                                                ) : (
                                                    <span className="text-xl sm:text-2xl">
                                                        🏪
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

                            {/* Description - CENTER (From previous version) */}
                            <motion.div
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8, delay: 0.2 }}
                                className="order-1 lg:order-2 flex flex-col justify-center min-h-80"
                                onScroll={handleUserInteraction}
                                onWheel={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onTouchMove={handleUserInteraction}
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
                                            transition:
                                                isSMEInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : {
                                                          duration: 0.6,
                                                          type: "spring",
                                                      },
                                        }}
                                        exit={{
                                            opacity: 0,
                                            x: 20,
                                            rotateY: -45,
                                            transition:
                                                isSMEInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : {
                                                          duration: 0.6,
                                                          type: "spring",
                                                      },
                                        }}
                                        className="text-white bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 lg:p-8 border border-white/10"
                                        onMouseDown={handleUserInteraction}
                                        onTouchStart={handleUserInteraction}
                                    >
                                        <motion.h3
                                            className="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold mb-4"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.2 }}
                                        >
                                            {smePlaces[selectedSMEPlace]
                                                ?.name || "Local Business"}
                                        </motion.h3>

                                        <motion.div
                                            className="text-sm sm:text-base lg:text-lg opacity-90 mb-4 sm:mb-6 leading-relaxed"
                                            initial={{ y: 20 }}
                                            animate={{ y: 0 }}
                                            transition={{ delay: 0.3 }}
                                        >
                                            <p className="line-clamp-3">
                                                {smePlaces[selectedSMEPlace]
                                                    ?.description
                                                    ? smePlaces[
                                                          selectedSMEPlace
                                                      ].description
                                                    : "Supporting local economy through quality products and services."}
                                            </p>
                                        </motion.div>
                                        <div className="space-y-2">
                                            <div className="flex items-center">
                                                <span className="text-orange-200">
                                                    📍
                                                </span>
                                                <span className="ml-2 text-sm">
                                                    {smePlaces[selectedSMEPlace]
                                                        ?.address ||
                                                        "Village Location"}
                                                </span>
                                            </div>
                                            {smePlaces[selectedSMEPlace]
                                                ?.phone_number && (
                                                <div className="flex items-center">
                                                    <span className="text-orange-200">
                                                        📞
                                                    </span>
                                                    <span className="ml-2 text-sm">
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
                                                    <div className="flex items-center">
                                                        <span className="text-orange-200">
                                                            🗺️
                                                        </span>
                                                        <span className="ml-2 text-sm">
                                                            {
                                                                smePlaces[
                                                                    selectedSMEPlace
                                                                ].latitude
                                                            }
                                                            ,{" "}
                                                            {
                                                                smePlaces[
                                                                    selectedSMEPlace
                                                                ].longitude
                                                            }
                                                        </span>
                                                    </div>
                                                )}

                                            <motion.button
                                                whileHover={{
                                                    scale: 1.05,
                                                    y: -2,
                                                }}
                                                whileTap={{ scale: 0.95 }}
                                                className="w-full mt-4 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-300"
                                                onClick={handleUserInteraction}
                                            >
                                                Visit Business →
                                            </motion.button>
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </motion.div>

                            {/* Interactive Business Map - RIGHT (Real Google Maps) */}
                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-3 sm:p-4 lg:p-6 order-2 lg:order-3 flex items-center justify-center min-h-80"
                                onMouseDown={handleUserInteraction}
                                onTouchStart={handleUserInteraction}
                                onWheel={handleUserInteraction}
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
                                            transition:
                                                isSMEInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : {
                                                          duration: 0.6,
                                                          type: "spring",
                                                      },
                                        }}
                                        exit={{
                                            scale: 0.8,
                                            opacity: 0,
                                            rotateY: 45,
                                            transition:
                                                isSMEInteracting ||
                                                isUserInteracting
                                                    ? { duration: 0 }
                                                    : {
                                                          duration: 0.6,
                                                          type: "spring",
                                                      },
                                        }}
                                        className="w-full h-60 sm:h-72 lg:h-80 bg-gradient-to-br from-orange-400/30 to-red-400/30 rounded-xl relative overflow-hidden"
                                        onMouseDown={handleUserInteraction}
                                        onTouchStart={handleUserInteraction}
                                        onWheel={handleUserInteraction}
                                    >
                                        {/* Real Google Maps embed - ACTIVE */}
                                        {smePlaces[selectedSMEPlace]
                                            ?.latitude &&
                                        smePlaces[selectedSMEPlace]
                                            ?.longitude ? (
                                            <div className="w-full h-full relative">
                                                <iframe
                                                    src={`https://maps.google.com/maps?q=${smePlaces[selectedSMEPlace].latitude},${smePlaces[selectedSMEPlace].longitude}&z=15&output=embed`}
                                                    width="100%"
                                                    height="100%"
                                                    style={{ border: 0 }}
                                                    className="rounded-xl"
                                                    onLoad={(e) => {
                                                        try {
                                                            const iframe =
                                                                e.target;
                                                            iframe.onmousedown =
                                                                handleUserInteraction;
                                                            iframe.ontouchstart =
                                                                handleUserInteraction;
                                                        } catch (error) {
                                                            console.log(
                                                                "Cannot add iframe interaction listeners due to CORS"
                                                            );
                                                        }
                                                    }}
                                                />

                                                {/* Overlay with place info */}
                                                <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 rounded-b-xl">
                                                    <h3 className="text-white font-semibold text-lg">
                                                        {smePlaces[
                                                            selectedSMEPlace
                                                        ]?.name ||
                                                            "Business Location"}
                                                    </h3>
                                                    <p className="text-white/80 text-sm">
                                                        📍{" "}
                                                        {smePlaces[
                                                            selectedSMEPlace
                                                        ]?.address ||
                                                            "Village Location"}
                                                    </p>
                                                    <a
                                                        href={`https://www.google.com/maps/search/?api=1&query=${smePlaces[selectedSMEPlace].latitude},${smePlaces[selectedSMEPlace].longitude}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-xs hover:bg-white/30 transition-colors"
                                                        onClick={
                                                            handleUserInteraction
                                                        }
                                                    >
                                                        Open in Maps →
                                                    </a>
                                                </div>
                                            </div>
                                        ) : (
                                            // Fallback for places without coordinates (Enhanced from previous version)
                                            <div className="w-full h-full flex items-center justify-center">
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

                                                    <motion.div
                                                        initial={{ width: 0 }}
                                                        animate={{
                                                            width: "60%",
                                                        }}
                                                        transition={{
                                                            delay: 0.5,
                                                            duration: 1,
                                                        }}
                                                        className="h-1 bg-gradient-to-r from-orange-300 to-red-300 mx-auto mt-4 rounded-full"
                                                    />
                                                </div>
                                            </div>
                                        )}
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
                    {/* Enhanced backdrop blur */}
                    <div className="absolute inset-0 backdrop-blur-sm" />

                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-3xl md:text-5xl font-bold text-white mb-4">
                                🛍️ Featured Products
                            </h2>
                            <motion.p
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                transition={{ delay: 0.3, duration: 0.8 }}
                                className="text-lg md:text-xl text-white/80 mb-6"
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
                                    className="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-indigo-500 to-blue-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300 text-sm md:text-base"
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
                    {/* Enhanced backdrop blur */}
                    <div className="absolute inset-0 backdrop-blur-sm" />

                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-3xl md:text-5xl font-bold text-white mb-4">
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
                                    className="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300 text-sm md:text-base"
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
                    {/* Enhanced backdrop blur */}
                    <div className="absolute inset-0 backdrop-blur-sm" />

                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-3xl md:text-5xl font-bold text-white mb-4">
                                📸 Village Gallery
                            </h2>
                            <motion.p
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                transition={{ delay: 0.3, duration: 0.8 }}
                                className="text-lg md:text-xl text-white/80 mb-6"
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
                        {/* Artistic Grid Layout (Enhanced from previous version) */}
                        <div className="grid grid-cols-12 gap-4 max-w-7xl mx-auto">
                            {featuredImages.slice(0, 12).map((image, index) => {
                                const gridPatterns = [
                                    "col-span-6 row-span-2", // Large
                                    "col-span-4 row-span-1", // Medium
                                    "col-span-3 row-span-1", // Small
                                    "col-span-4 row-span-2", // Tall
                                    "col-span-5 row-span-1", // Wide
                                    "col-span-3 row-span-1", // Small
                                    "col-span-6 row-span-1", // Wide medium
                                    "col-span-3 row-span-2", // Tall small
                                ];

                                const aspectRatios = [
                                    "aspect-video",
                                    "aspect-square",
                                    "aspect-[4/3]",
                                    "aspect-[3/4]",
                                    "aspect-[16/10]",
                                ];

                                return (
                                    <motion.div
                                        key={image.id}
                                        initial={{
                                            opacity: 0,
                                            scale: 0.6,
                                            rotateY: 45,
                                            z: -100,
                                        }}
                                        whileInView={{
                                            opacity: 1,
                                            scale: 1,
                                            rotateY: 0,
                                            z: 0,
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
                                            transition: { duration: 0.3 },
                                        }}
                                        className={`${
                                            gridPatterns[
                                                index % gridPatterns.length
                                            ]
                                        } cursor-pointer group relative overflow-hidden rounded-2xl transform-gpu perspective-1000`}
                                        onClick={() => openLightbox(image)}
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

                                            {/* Geometric hover indicators */}
                                            <motion.div
                                                initial={{
                                                    scale: 0,
                                                    rotate: -90,
                                                }}
                                                whileHover={{
                                                    scale: 1,
                                                    rotate: 0,
                                                }}
                                                transition={{ duration: 0.3 }}
                                                className="absolute top-4 right-4 w-8 h-8 border-2 border-white/80 rounded-full flex items-center justify-center backdrop-blur-sm"
                                            >
                                                <motion.span
                                                    animate={{ rotate: 360 }}
                                                    transition={{
                                                        duration: 2,
                                                        repeat: Infinity,
                                                        ease: "linear",
                                                    }}
                                                    className="text-white text-sm"
                                                >
                                                    ↗
                                                </motion.span>
                                            </motion.div>

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
                                                    <motion.div
                                                        initial={{
                                                            x: -20,
                                                            opacity: 0,
                                                        }}
                                                        whileHover={{
                                                            x: 0,
                                                            opacity: 1,
                                                        }}
                                                        transition={{
                                                            delay: 0.1,
                                                        }}
                                                        className="text-xs text-purple-300 mb-1 flex items-center"
                                                    >
                                                        <span className="mr-1">
                                                            📍
                                                        </span>
                                                        {image.place.name}
                                                    </motion.div>
                                                )}

                                                {image.caption && (
                                                    <motion.div
                                                        initial={{
                                                            x: -20,
                                                            opacity: 0,
                                                        }}
                                                        whileHover={{
                                                            x: 0,
                                                            opacity: 1,
                                                        }}
                                                        transition={{
                                                            delay: 0.2,
                                                        }}
                                                        className="text-sm text-white font-medium line-clamp-3 leading-relaxed h-[68px] overflow-hidden"
                                                    >
                                                        {image.caption}
                                                    </motion.div>
                                                )}

                                                <motion.div
                                                    initial={{
                                                        x: -20,
                                                        opacity: 0,
                                                    }}
                                                    whileHover={{
                                                        x: 0,
                                                        opacity: 1,
                                                    }}
                                                    transition={{ delay: 0.3 }}
                                                    className="text-xs text-gray-300 mt-2 flex items-center justify-between"
                                                >
                                                    <span>
                                                        {new Date(
                                                            image.created_at
                                                        ).toLocaleDateString()}
                                                    </span>
                                                    <motion.span
                                                        animate={{
                                                            scale: [1, 1.2, 1],
                                                        }}
                                                        transition={{
                                                            duration: 2,
                                                            repeat: Infinity,
                                                        }}
                                                        className="text-pink-300"
                                                    >
                                                        ❤️
                                                    </motion.span>
                                                </motion.div>
                                            </motion.div>

                                            {/* Artistic corner decorations */}
                                            <motion.div
                                                initial={{ scale: 0 }}
                                                whileInView={{ scale: 1 }}
                                                transition={{
                                                    delay: index * 0.05 + 0.5,
                                                }}
                                                className="absolute top-0 left-0 w-0 h-0 border-l-[20px] border-t-[20px] border-l-transparent border-t-white/20"
                                            />
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
                                    className="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center mx-auto text-sm md:text-base"
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

            {/* Lightbox Modal */}
            <AnimatePresence>
                {isLightboxOpen && selectedImage && (
                    <LightboxModal
                        image={selectedImage}
                        onClose={closeLightbox}
                        onNavigate={navigateImage}
                        currentIndex={
                            featuredImages?.findIndex(
                                (img) => img.id === selectedImage.id
                            ) || 0
                        }
                        totalImages={featuredImages?.length || 0}
                    />
                )}
            </AnimatePresence>
        </MainLayout>
    );
};

export default Home;
