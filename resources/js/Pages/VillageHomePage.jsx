import React, { useState, useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
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
    // Add these state variables at the top with your other useState declarations
    const [isTourismInteracting, setIsTourismInteracting] = useState(false);
    const [isSMEInteracting, setIsSMEInteracting] = useState(false);
    const [isUserInteracting, setIsUserInteracting] = useState(false); // New global interaction state
    const [interactionTimeout, setInteractionTimeout] = useState(null); // Timeout for interaction detection

    // Add this function to handle user interaction detection
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

    // Audio configuration array for flexible management
    const audioConfig = [
        {
            id: "all",
            src: "/audio/sasakbacksong.mp3",
            volume: 0.25,
            loop: true,
            fadeIn: true,
            fadeOut: true,
        },
    ];

    // Enhanced audio management with fade effects
    const audioManager = useRef(new Map());
    const fadeTimeouts = useRef(new Map());

    // Initialize audio manager
    useEffect(() => {
        audioConfig.forEach((config, index) => {
            const audio = new Audio(config.src);
            audio.volume = 0; // Start with 0 volume for fade in
            audio.loop = config.loop;
            audio.preload = "auto";

            audioManager.current.set(index, {
                audio: audio,
                config: config,
                targetVolume: config.volume,
                isPlaying: false,
            });
        });

        return () => {
            // Cleanup
            audioManager.current.forEach((audioData) => {
                audioData.audio.pause();
                audioData.audio.currentTime = 0;
            });
            fadeTimeouts.current.forEach((timeout) => clearTimeout(timeout));
        };
    }, []);

    const [currentSection, setCurrentSection] = useState(0);
    const [selectedTourismPlace, setSelectedTourismPlace] = useState(0);
    const [selectedSME, setSelectedSME] = useState(0);
    const [isPlaying, setIsPlaying] = useState(true);

    // Audio refs for each section
    const audioRefs = useRef([]);
    const { scrollY } = useScroll();

    // Parallax transforms
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);

    // Section refs for intersection observer
    const [heroRef, heroInView] = useInView({ threshold: 0.3 });
    const [tourismRef, tourismInView] = useInView({ threshold: 0.3 });
    const [smeRef, smeInView] = useInView({ threshold: 0.3 });
    const [productsRef, productsInView] = useInView({ threshold: 0.3 });
    const [articlesRef, articlesInView] = useInView({ threshold: 0.3 });
    const [galleryRef, galleryInView] = useInView({
        threshold: 0.1, // Lower threshold
        rootMargin: "0px 0px -10% 0px", // Trigger earlier
    });

    // Update the auto-scroll tourism places effect
    useEffect(() => {
        if (isUserInteracting || isTourismInteracting) {
            return; // Don't auto-scroll if user is interacting
        }

        const interval = setInterval(() => {
            if (tourismPlaces.length > 0) {
                setSelectedTourismPlace(
                    (prev) => (prev + 1) % tourismPlaces.length
                );
            }
        }, 5000);
        return () => clearInterval(interval);
    }, [tourismPlaces.length, isUserInteracting, isTourismInteracting]);

    // Update the auto-scroll SME places effect
    useEffect(() => {
        if (isUserInteracting || isSMEInteracting) {
            return; // Don't auto-scroll if user is interacting
        }

        const interval = setInterval(() => {
            if (smePlaces.length > 0) {
                setSelectedSME((prev) => (prev + 1) % smePlaces.length);
            }
        }, 5000);
        return () => clearInterval(interval);
    }, [smePlaces.length, isUserInteracting, isSMEInteracting]);

    const [scrollBasedSection, setScrollBasedSection] = useState(0);

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

    useEffect(() => {
        const handleScroll = () => {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            // Calculate scroll percentage
            const scrollPercentage =
                scrollPosition / (documentHeight - windowHeight);

            // If we're near the bottom (90%+), force gallery section
            if (scrollPercentage >= 0.9) {
                setScrollBasedSection(4);
            } else if (scrollPercentage >= 0.7) {
                setScrollBasedSection(3);
            } else if (scrollPercentage >= 0.5) {
                setScrollBasedSection(2);
            } else if (scrollPercentage >= 0.25) {
                setScrollBasedSection(1);
            } else {
                setScrollBasedSection(0);
            }
        };

        window.addEventListener("scroll", handleScroll);
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    // Modified section detection that combines both approaches:
    useEffect(() => {
        let newSection = currentSection;

        if (galleryInView) {
            newSection = 5; // Updated to 5
        } else if (productsInView && !galleryInView) {
            newSection = 4; // New products section
        } else if (articlesInView && !productsInView) {
            newSection = 3;
        } else if (smeInView && !articlesInView) {
            newSection = 2;
        } else if (tourismInView && !smeInView) {
            newSection = 1;
        } else if (heroInView && !tourismInView) {
            newSection = 0;
        } else {
            newSection = scrollBasedSection;
        }

        if (newSection !== currentSection) {
            setCurrentSection(newSection);
        }
    }, [
        heroInView,
        tourismInView,
        smeInView,
        articlesInView,
        productsInView, // Add this
        galleryInView,
        scrollBasedSection,
        currentSection,
    ]);

    // Enhanced audio switching with fade effects
    // useEffect(() => {
    //     if (!isPlaying) {
    //         // Fade out all audio when music is turned off
    //         audioManager.current.forEach((audioData, index) => {
    //             if (audioData.isPlaying) {
    //                 fadeOut(index);
    //             }
    //         });
    //         return;
    //     }

    //     // Switch to current section's audio
    //     audioManager.current.forEach((audioData, index) => {
    //         if (index === currentSection) {
    //             if (!audioData.isPlaying) {
    //                 fadeIn(index);
    //             }
    //         } else {
    //             if (audioData.isPlaying) {
    //                 fadeOut(index);
    //             }
    //         }
    //     });
    // }, [currentSection, isPlaying]);

    // Fade in function
    const fadeIn = (sectionIndex) => {
        const audioData = audioManager.current.get(sectionIndex);
        if (!audioData) return;

        const { audio, config } = audioData;

        // Clear any existing fade timeout
        if (fadeTimeouts.current.has(sectionIndex)) {
            clearTimeout(fadeTimeouts.current.get(sectionIndex));
        }

        audio.currentTime = 0;
        audio.volume = 0;
        audioData.isPlaying = true;

        audio.play().catch(console.log);

        if (config.fadeIn) {
            const fadeStep = audioData.targetVolume / 20; // 20 steps for smooth fade
            const fadeInterval = 100; // 100ms per step

            const fade = () => {
                if (audio.volume < audioData.targetVolume - fadeStep) {
                    audio.volume = Math.min(
                        audio.volume + fadeStep,
                        audioData.targetVolume
                    );
                    const timeout = setTimeout(fade, fadeInterval);
                    fadeTimeouts.current.set(sectionIndex, timeout);
                } else {
                    audio.volume = audioData.targetVolume;
                }
            };
            fade();
        } else {
            audio.volume = audioData.targetVolume;
        }
    };

    // Fade out function
    const fadeOut = (sectionIndex) => {
        const audioData = audioManager.current.get(sectionIndex);
        if (!audioData) return;

        const { audio, config } = audioData;

        // Clear any existing fade timeout
        if (fadeTimeouts.current.has(sectionIndex)) {
            clearTimeout(fadeTimeouts.current.get(sectionIndex));
        }

        if (config.fadeOut && audio.volume > 0) {
            const fadeStep = audio.volume / 20; // 20 steps for smooth fade
            const fadeInterval = 50; // 50ms per step for faster fade out

            const fade = () => {
                if (audio.volume > fadeStep) {
                    audio.volume = Math.max(audio.volume - fadeStep, 0);
                    const timeout = setTimeout(fade, fadeInterval);
                    fadeTimeouts.current.set(sectionIndex, timeout);
                } else {
                    audio.volume = 0;
                    audio.pause();
                    audioData.isPlaying = false;
                }
            };
            fade();
        } else {
            audio.volume = 0;
            audio.pause();
            audioData.isPlaying = false;
        }
    };

    useEffect(() => {
        if (!isPlaying) {
            // Fade out the single audio when music is turned off
            const audioData = audioManager.current.get(0);
            if (audioData && audioData.isPlaying) {
                fadeOut(0);
            }
            return;
        }

        // Play the single audio if not already playing
        const audioData = audioManager.current.get(0);
        if (audioData && !audioData.isPlaying) {
            fadeIn(0);
        }
    }, [isPlaying, fadeIn, fadeOut]); // Remove currentSection from dependencies

    // Enhanced toggle music function
    const toggleMusic = () => {
        setIsPlaying((prev) => {
            const newState = !prev;

            if (!newState) {
                // Fade out all audio when turning off
                audioManager.current.forEach((audioData, index) => {
                    if (audioData.isPlaying) {
                        fadeOut(index);
                    }
                });
            } else {
                // Fade in current section's audio when turning on
                const currentAudioData =
                    audioManager.current.get(currentSection);
                if (currentAudioData && !currentAudioData.isPlaying) {
                    fadeIn(currentSection);
                }
            }

            return newState;
        });
    };

    return (
        <MainLayout title={`Welcome to ${village?.name}`}>
            <Head title={`${village?.name} - Smart Village`} />

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
                {/* Base overlay */}
                <div className="absolute inset-0 bg-black/20" />
            </div>

            {/* SMOOTH COLOR OVERLAY - This creates the seamless transitions */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{
                    background: useTransform(
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
                    ),
                }}
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

            {/* Hero Section - CLEANED UP */}
            <section
                ref={heroRef}
                className="relative h-screen overflow-hidden z-10"
            >
                <div className="absolute inset-0 backdrop-blur-[1px]" />

                {/* Hero Content */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 flex items-center justify-center text-center z-20"
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

            {/* Tourism Section - UPDATED */}
            <section
                ref={tourismRef}
                className="min-h-screen relative overflow-hidden py-20 z-10"
                onMouseEnter={() => {
                    setIsTourismInteracting(true);
                    handleUserInteraction();
                }}
                onMouseLeave={() => setIsTourismInteracting(false)}
                onTouchStart={handleUserInteraction}
                onWheel={handleUserInteraction}
            >
                <div className="absolute inset-0 backdrop-blur-sm" />

                <div className="container mx-auto px-6 h-full relative z-10">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-center mb-16"
                    >
                        <h2 className="text-5xl font-bold text-white mb-4">
                            Tourism Destinations
                        </h2>
                        <motion.div
                            initial={{ width: 0 }}
                            whileInView={{ width: "10rem" }}
                            transition={{ delay: 0.5, duration: 1 }}
                            className="h-1 bg-gradient-to-r from-indigo-400 to-blue-400 mx-auto"
                        />
                    </motion.div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                        {/* Interactive Map - Left */}
                        <motion.div
                            initial={{ opacity: 0, x: -50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            className="bg-white/10 backdrop-blur-md rounded-2xl p-6 flex items-center justify-center"
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
                                    className="w-full h-80 bg-gradient-to-br from-green-400/30 to-blue-400/30 rounded-xl relative overflow-hidden"
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                    onWheel={handleUserInteraction}
                                >
                                    {/* Real Map Component - No API Key Required */}
                                    {tourismPlaces[selectedTourismPlace]
                                        ?.latitude &&
                                    tourismPlaces[selectedTourismPlace]
                                        ?.longitude ? (
                                        <div className="w-full h-full relative">
                                            {/* Google Maps embed - ACTIVE */}
                                            <iframe
                                                src={`https://maps.google.com/maps?q=${tourismPlaces[selectedTourismPlace].latitude},${tourismPlaces[selectedTourismPlace].longitude}&z=15&output=embed`}
                                                width="100%"
                                                height="100%"
                                                style={{ border: 0 }}
                                                className="rounded-xl"
                                                onLoad={(e) => {
                                                    try {
                                                        const iframe = e.target;
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
                                                    animate={{ y: [0, -10, 0] }}
                                                    transition={{
                                                        duration: 2,
                                                        repeat: Infinity,
                                                    }}
                                                    className="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4"
                                                >
                                                    📍
                                                </motion.div>
                                                <h3 className="text-xl font-semibold mb-2">
                                                    {tourismPlaces[
                                                        selectedTourismPlace
                                                    ]?.name ||
                                                        "Beautiful Destination"}
                                                </h3>
                                                <p className="text-sm opacity-75">
                                                    Interactive location view
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </motion.div>
                            </AnimatePresence>
                        </motion.div>

                        {/* Description - Center */}
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="flex flex-col justify-center max-h-96"
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
                                    className="text-white h-full overflow-y-auto"
                                    onScroll={handleUserInteraction}
                                    onWheel={handleUserInteraction}
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                >
                                    <h3 className="text-3xl font-bold mb-4">
                                        {tourismPlaces[selectedTourismPlace]
                                            ?.name || "Beautiful Destination"}
                                    </h3>
                                    <div
                                        className="text-lg opacity-90 mb-6 leading-relaxed max-h-32 overflow-y-auto"
                                        onScroll={handleUserInteraction}
                                        onWheel={handleUserInteraction}
                                    >
                                        <p className="line-clamp-4">
                                            {tourismPlaces[selectedTourismPlace]
                                                ?.description
                                                ? tourismPlaces[
                                                      selectedTourismPlace
                                                  ].description.length > 200
                                                    ? tourismPlaces[
                                                          selectedTourismPlace
                                                      ].description.substring(
                                                          0,
                                                          200
                                                      ) + "..."
                                                    : tourismPlaces[
                                                          selectedTourismPlace
                                                      ].description
                                                : "Explore the natural beauty and cultural richness of this amazing destination."}
                                        </p>
                                    </div>
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
                                            tourismPlaces[selectedTourismPlace]
                                                ?.longitude && (
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
                                    </div>
                                </motion.div>
                            </AnimatePresence>
                        </motion.div>

                        {/* Enhanced Places List - Right */}
                        <motion.div
                            initial={{ opacity: 0, x: 50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8, delay: 0.4 }}
                            className="space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20 pr-2"
                            onScroll={handleUserInteraction}
                            onWheel={handleUserInteraction}
                            onTouchStart={handleUserInteraction}
                            onTouchMove={handleUserInteraction}
                        >
                            {tourismPlaces.slice(0, 6).map((place, index) => (
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
                                    className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                        selectedTourismPlace === index
                                            ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                            : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                    }`}
                                    style={{ overflow: "visible" }}
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                >
                                    <div className="flex items-center space-x-4">
                                        <motion.div
                                            className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
                                            whileHover={{ rotate: 5 }}
                                        >
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
                                            {selectedTourismPlace === index && (
                                                <motion.div
                                                    layoutId="selection-indicator"
                                                    className="absolute inset-0 border-2 border-white rounded-lg"
                                                />
                                            )}
                                        </motion.div>

                                        <div className="flex-1 text-white min-w-0">
                                            <h4 className="font-semibold text-lg truncate">
                                                {place.name}
                                            </h4>
                                            <p className="text-sm opacity-75 truncate">
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

            {/* SME Section - UPDATED */}
            <section
                ref={smeRef}
                className="min-h-screen relative overflow-hidden py-20 z-10"
                onMouseEnter={() => {
                    setIsSMEInteracting(true);
                    handleUserInteraction();
                }}
                onMouseLeave={() => setIsSMEInteracting(false)}
                onTouchStart={handleUserInteraction}
                onWheel={handleUserInteraction}
            >
                <div className="absolute inset-0 backdrop-blur-sm" />

                <div className="container mx-auto px-6 h-full relative z-10">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-center mb-16"
                    >
                        <h2 className="text-5xl font-bold text-white mb-4">
                            Local Businesses
                        </h2>
                        <motion.div
                            initial={{ width: 0 }}
                            whileInView={{ width: "10rem" }}
                            transition={{ delay: 0.5, duration: 1 }}
                            className="h-1 bg-gradient-to-r from-indigo-400 to-blue-400 mx-auto"
                        />
                    </motion.div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                        {/* Enhanced SME Places List - LEFT */}
                        <motion.div
                            initial={{ opacity: 0, x: -50 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            className="space-y-4 max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-white/20 pr-2"
                            onScroll={handleUserInteraction}
                            onWheel={handleUserInteraction}
                            onTouchStart={handleUserInteraction}
                            onTouchMove={handleUserInteraction}
                        >
                            {smePlaces.slice(0, 6).map((place, index) => (
                                <motion.div
                                    key={place.id}
                                    onClick={() => {
                                        setSelectedSME(index);
                                        handleUserInteraction();
                                    }}
                                    layoutId={`sme-${place.id}`}
                                    whileHover={{
                                        scale: 1.02,
                                        boxShadow:
                                            "0 10px 30px rgba(255,255,255,0.1)",
                                    }}
                                    whileTap={{ scale: 0.98 }}
                                    className={`p-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                        selectedSME === index
                                            ? "bg-white/20 backdrop-blur-md border border-white/30 shadow-lg"
                                            : "bg-white/10 backdrop-blur-sm hover:bg-white/15"
                                    }`}
                                    style={{ overflow: "visible" }}
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                >
                                    <div className="flex items-center space-x-4">
                                        <motion.div
                                            animate={{
                                                scale:
                                                    selectedSME === index
                                                        ? 1.2
                                                        : 1,
                                                opacity:
                                                    selectedSME === index
                                                        ? 1
                                                        : 0.3,
                                            }}
                                            className="w-3 h-3 rounded-full bg-white flex-shrink-0"
                                        />

                                        <div className="flex-1 text-white min-w-0">
                                            <h4 className="font-semibold text-lg truncate">
                                                {place.name}
                                            </h4>
                                            <p className="text-sm opacity-75 truncate">
                                                {place.category?.name}
                                            </p>
                                        </div>

                                        <motion.div
                                            className="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
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
                                                    🏪
                                                </span>
                                            )}
                                            {selectedSME === index && (
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

                        {/* Description - CENTER */}
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="flex flex-col justify-center max-h-96"
                            onScroll={handleUserInteraction}
                            onWheel={handleUserInteraction}
                            onTouchStart={handleUserInteraction}
                            onTouchMove={handleUserInteraction}
                        >
                            <AnimatePresence mode="wait">
                                <motion.div
                                    key={selectedSME}
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
                                    className="text-white bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 h-full overflow-y-auto"
                                    onScroll={handleUserInteraction}
                                    onWheel={handleUserInteraction}
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                >
                                    <motion.h3
                                        className="text-3xl font-bold mb-4"
                                        initial={{ y: 20 }}
                                        animate={{ y: 0 }}
                                        transition={{ delay: 0.2 }}
                                    >
                                        {smePlaces[selectedSME]?.name ||
                                            "Local Business"}
                                    </motion.h3>

                                    <motion.div
                                        className="text-lg opacity-90 mb-6 leading-relaxed max-h-32 overflow-y-auto"
                                        initial={{ y: 20 }}
                                        animate={{ y: 0 }}
                                        transition={{ delay: 0.3 }}
                                        onScroll={handleUserInteraction}
                                        onWheel={handleUserInteraction}
                                    >
                                        <p className="line-clamp-4">
                                            {smePlaces[selectedSME]?.description
                                                ? smePlaces[selectedSME]
                                                      .description.length > 200
                                                    ? smePlaces[
                                                          selectedSME
                                                      ].description.substring(
                                                          0,
                                                          200
                                                      ) + "..."
                                                    : smePlaces[selectedSME]
                                                          .description
                                                : "Supporting local economy through quality products and services."}
                                        </p>
                                    </motion.div>

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
                                                {smePlaces[selectedSME]
                                                    ?.address ||
                                                    "Village Location"}
                                            </span>
                                        </div>

                                        {smePlaces[selectedSME]
                                            ?.phone_number && (
                                            <div className="flex items-center p-3 bg-orange-500/20 rounded-lg">
                                                <span className="text-orange-200 text-xl mr-3">
                                                    📞
                                                </span>
                                                <span className="text-sm">
                                                    {
                                                        smePlaces[selectedSME]
                                                            .phone_number
                                                    }
                                                </span>
                                            </div>
                                        )}

                                        {smePlaces[selectedSME]?.latitude &&
                                            smePlaces[selectedSME]
                                                ?.longitude && (
                                                <div className="flex items-center p-3 bg-orange-500/20 rounded-lg">
                                                    <span className="text-orange-200 text-xl mr-3">
                                                        🗺️
                                                    </span>
                                                    <span className="text-sm">
                                                        {
                                                            smePlaces[
                                                                selectedSME
                                                            ].latitude
                                                        }
                                                        ,{" "}
                                                        {
                                                            smePlaces[
                                                                selectedSME
                                                            ].longitude
                                                        }
                                                    </span>
                                                </div>
                                            )}

                                        <motion.button
                                            whileHover={{ scale: 1.05, y: -2 }}
                                            whileTap={{ scale: 0.95 }}
                                            className="w-full mt-4 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-300"
                                            onClick={handleUserInteraction}
                                        >
                                            Visit Business →
                                        </motion.button>
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
                            onMouseDown={handleUserInteraction}
                            onTouchStart={handleUserInteraction}
                            onWheel={handleUserInteraction}
                        >
                            <AnimatePresence mode="wait">
                                <motion.div
                                    key={selectedSME}
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
                                    className="w-full h-80 bg-gradient-to-br from-orange-400/30 to-red-400/30 rounded-xl relative overflow-hidden"
                                    onMouseDown={handleUserInteraction}
                                    onTouchStart={handleUserInteraction}
                                    onWheel={handleUserInteraction}
                                >
                                    {/* Real Map Component - No API Key Required */}
                                    {smePlaces[selectedSME]?.latitude &&
                                    smePlaces[selectedSME]?.longitude ? (
                                        <div className="w-full h-full relative">
                                            {/* Google Maps embed - ACTIVE */}
                                            <iframe
                                                src={`https://maps.google.com/maps?q=${smePlaces[selectedSME].latitude},${smePlaces[selectedSME].longitude}&z=15&output=embed`}
                                                width="100%"
                                                height="100%"
                                                style={{ border: 0 }}
                                                className="rounded-xl"
                                                onLoad={(e) => {
                                                    try {
                                                        const iframe = e.target;
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
                                                    {smePlaces[selectedSME]
                                                        ?.name ||
                                                        "Business Location"}
                                                </h3>
                                                <p className="text-white/80 text-sm">
                                                    📍{" "}
                                                    {smePlaces[selectedSME]
                                                        ?.address ||
                                                        "Village Location"}
                                                </p>
                                                <a
                                                    href={`https://www.google.com/maps/search/?api=1&query=${smePlaces[selectedSME].latitude},${smePlaces[selectedSME].longitude}`}
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
                                                    {smePlaces[selectedSME]
                                                        ?.name ||
                                                        "Business Location"}
                                                </h3>
                                                <p className="text-sm opacity-75">
                                                    Interactive business view
                                                </p>

                                                <motion.div
                                                    initial={{ width: 0 }}
                                                    animate={{ width: "60%" }}
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

            {/* Products Section - CLEANED UP */}
            <section
                ref={productsRef}
                className="min-h-screen relative overflow-hidden py-20 z-10"
            >
                <div className="absolute inset-0 backdrop-blur-sm" />

                <div className="container mx-auto px-6 relative z-10">
                    {/* Your existing products content here - just remove the style prop and transitions */}
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
                    {/* Products Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {products?.slice(0, 6).map((product, index) => (
                            <motion.div
                                key={product.id}
                                initial={{ opacity: 0, y: 100, scale: 0.8 }}
                                whileInView={{ opacity: 1, y: 0, scale: 1 }}
                                transition={{
                                    duration: 0.8,
                                    delay: index * 0.1,
                                    type: "spring",
                                    stiffness: 100,
                                }}
                                whileHover={{
                                    y: -10,
                                    scale: 1.03,
                                    transition: { duration: 0.3 },
                                }}
                                className="group"
                            >
                                <div className="bg-white/10 backdrop-blur-md rounded-2xl overflow-hidden border border-white/20 hover:border-white/40 transition-all duration-500">
                                    <div className="relative h-56 overflow-hidden">
                                        {product.primary_image_url ? (
                                            <motion.img
                                                src={product.primary_image_url}
                                                alt={product.name}
                                                className="w-full h-full object-cover"
                                                whileHover={{ scale: 1.1 }}
                                                transition={{ duration: 0.5 }}
                                            />
                                        ) : (
                                            <div className="w-full h-full bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center">
                                                <motion.span
                                                    className="text-4xl text-white"
                                                    animate={{
                                                        rotate: [0, 5, -5, 0],
                                                        scale: [1, 1.1, 1],
                                                    }}
                                                    transition={{
                                                        duration: 2,
                                                        repeat: Infinity,
                                                        delay: index * 0.2,
                                                    }}
                                                >
                                                    🛍️
                                                </motion.span>
                                            </div>
                                        )}

                                        {/* Featured badge */}
                                        {product.is_featured && (
                                            <motion.div
                                                initial={{
                                                    scale: 0,
                                                    rotate: -45,
                                                }}
                                                animate={{
                                                    scale: 1,
                                                    rotate: 0,
                                                }}
                                                className="absolute top-4 left-4 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-semibold"
                                            >
                                                ⭐ Featured
                                            </motion.div>
                                        )}

                                        {/* Hover overlay */}
                                        <motion.div
                                            initial={{ opacity: 0 }}
                                            whileHover={{ opacity: 1 }}
                                            className="absolute inset-0 bg-black/40 flex items-center justify-center"
                                        >
                                            <motion.div
                                                initial={{
                                                    scale: 0,
                                                    rotate: -180,
                                                }}
                                                whileHover={{
                                                    scale: 1,
                                                    rotate: 0,
                                                }}
                                                transition={{ duration: 0.3 }}
                                                className="bg-white/20 backdrop-blur-sm rounded-full p-4"
                                            >
                                                <span className="text-white text-xl">
                                                    👁️
                                                </span>
                                            </motion.div>
                                        </motion.div>
                                    </div>

                                    <div className="p-6">
                                        <motion.h3
                                            className="text-xl font-bold text-white mb-2 line-clamp-2 group-hover:text-indigo-200 transition-colors duration-300"
                                            initial={{ y: 20 }}
                                            whileInView={{ y: 0 }}
                                            transition={{
                                                delay: index * 0.1 + 0.2,
                                            }}
                                        >
                                            {product.name}
                                        </motion.h3>

                                        <motion.p
                                            className="text-white/70 text-sm line-clamp-2 mb-4"
                                            initial={{ y: 20, opacity: 0 }}
                                            whileInView={{ y: 0, opacity: 1 }}
                                            transition={{
                                                delay: index * 0.1 + 0.3,
                                            }}
                                        >
                                            {product.short_description ||
                                                product.description
                                                    ?.replace(/<[^>]*>/g, "")
                                                    .substring(0, 100)}
                                        </motion.p>

                                        <motion.div
                                            initial={{ y: 20, opacity: 0 }}
                                            whileInView={{ y: 0, opacity: 1 }}
                                            transition={{
                                                delay: index * 0.1 + 0.4,
                                            }}
                                            className="flex items-center justify-between"
                                        >
                                            <div className="text-indigo-200 font-semibold">
                                                {product.display_price ||
                                                    "Contact for price"}
                                            </div>
                                            <motion.button
                                                whileHover={{
                                                    scale: 1.1,
                                                    rotate: 5,
                                                }}
                                                whileTap={{ scale: 0.95 }}
                                                className="bg-indigo-500 hover:bg-indigo-400 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-300"
                                            >
                                                View →
                                            </motion.button>
                                        </motion.div>

                                        {/* Product info */}
                                        <motion.div
                                            initial={{ y: 20, opacity: 0 }}
                                            whileInView={{ y: 0, opacity: 1 }}
                                            transition={{
                                                delay: index * 0.1 + 0.5,
                                            }}
                                            className="mt-4 space-y-1"
                                        >
                                            {product.place && (
                                                <div className="flex items-center text-xs text-white/60">
                                                    <span className="mr-1">
                                                        🏪
                                                    </span>
                                                    {product.place.name}
                                                </div>
                                            )}
                                            {product.category && (
                                                <div className="flex items-center text-xs text-white/60">
                                                    <span className="mr-1">
                                                        🏷️
                                                    </span>
                                                    {product.category.name}
                                                </div>
                                            )}
                                        </motion.div>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </div>

                    {/* Call to action */}
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1, duration: 0.8 }}
                        className="text-center mt-12"
                    >
                        <motion.button
                            whileHover={{ scale: 1.05, y: -3 }}
                            whileTap={{ scale: 0.95 }}
                            className="px-8 py-4 bg-gradient-to-r from-indigo-500 to-blue-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            View All Products →
                        </motion.button>
                    </motion.div>
                </div>
            </section>

            {/* Articles Section - CLEANED UP */}
            <section
                ref={articlesRef}
                className="min-h-screen relative overflow-hidden py-20 z-10"
            >
                <div className="absolute inset-0 backdrop-blur-sm" />

                <div className="container mx-auto px-6 relative z-10">
                    {/* Your existing articles content here - just remove the style prop and transitions */}
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

                    {/* Articles Grid with staggered animations */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {articles?.slice(0, 6).map((article, index) => (
                            <motion.div
                                key={article.id}
                                initial={{ opacity: 0, y: 100, rotateX: 45 }}
                                whileInView={{ opacity: 1, y: 0, rotateX: 0 }}
                                transition={{
                                    duration: 0.8,
                                    delay: index * 0.15,
                                    type: "spring",
                                    stiffness: 100,
                                }}
                                whileHover={{
                                    y: -20,
                                    scale: 1.05,
                                    rotateY: 5,
                                    transition: { duration: 0.3 },
                                }}
                                className="group perspective-1000"
                            >
                                <Link href={`/articles/${article.slug}`}>
                                    <div className="bg-white/10 backdrop-blur-md rounded-2xl overflow-hidden border border-white/20 hover:border-white/40 transition-all duration-500 transform-gpu">
                                        <div className="relative h-48 overflow-hidden">
                                            {article.cover_image_url ? (
                                                <motion.img
                                                    src={
                                                        article.cover_image_url
                                                    }
                                                    alt={article.title}
                                                    className="w-full h-full object-cover"
                                                    whileHover={{ scale: 1.1 }}
                                                    transition={{
                                                        duration: 0.5,
                                                    }}
                                                />
                                            ) : (
                                                <div className="w-full h-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center relative">
                                                    <motion.span
                                                        className="text-4xl text-white"
                                                        animate={{
                                                            rotate: [
                                                                0, 10, -10, 0,
                                                            ],
                                                            scale: [1, 1.1, 1],
                                                        }}
                                                        transition={{
                                                            duration: 2,
                                                            repeat: Infinity,
                                                            delay: index * 0.2,
                                                        }}
                                                    >
                                                        📖
                                                    </motion.span>

                                                    {/* Floating particles */}
                                                    <motion.div
                                                        animate={{
                                                            y: [0, -20, 0],
                                                            opacity: [
                                                                0.3, 1, 0.3,
                                                            ],
                                                        }}
                                                        transition={{
                                                            duration: 3,
                                                            repeat: Infinity,
                                                            delay: index * 0.5,
                                                        }}
                                                        className="absolute top-4 right-4 w-2 h-2 bg-white/60 rounded-full"
                                                    />
                                                </div>
                                            )}

                                            {/* Hover overlay */}
                                            <motion.div
                                                initial={{ opacity: 0 }}
                                                whileHover={{ opacity: 1 }}
                                                className="absolute inset-0 bg-black/40 flex items-center justify-center"
                                            >
                                                <motion.div
                                                    initial={{
                                                        scale: 0,
                                                        rotate: -180,
                                                    }}
                                                    whileHover={{
                                                        scale: 1,
                                                        rotate: 0,
                                                    }}
                                                    transition={{
                                                        duration: 0.3,
                                                    }}
                                                    className="bg-white/20 backdrop-blur-sm rounded-full p-4"
                                                >
                                                    <span className="text-white text-xl">
                                                        👁️
                                                    </span>
                                                </motion.div>
                                            </motion.div>
                                        </div>

                                        <div className="p-6">
                                            <motion.h3
                                                className="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-blue-200 transition-colors duration-300"
                                                initial={{ y: 20 }}
                                                whileInView={{ y: 0 }}
                                                transition={{
                                                    delay: index * 0.1 + 0.3,
                                                }}
                                            >
                                                {article.title}
                                            </motion.h3>

                                            <motion.p
                                                className="text-white/70 text-sm line-clamp-3 mb-4 leading-relaxed"
                                                initial={{ y: 20, opacity: 0 }}
                                                whileInView={{
                                                    y: 0,
                                                    opacity: 1,
                                                }}
                                                transition={{
                                                    delay: index * 0.1 + 0.4,
                                                }}
                                            >
                                                {article.content
                                                    ?.replace(/<[^>]*>/g, "")
                                                    .substring(0, 120)}
                                                ...
                                            </motion.p>

                                            <motion.button
                                                whileHover={{
                                                    scale: 1.05,
                                                    x: 5,
                                                }}
                                                whileTap={{ scale: 0.95 }}
                                                className="text-blue-200 hover:text-white transition-all duration-300 text-sm font-semibold flex items-center group"
                                            >
                                                Read Story
                                                <motion.span
                                                    className="ml-2"
                                                    animate={{ x: [0, 5, 0] }}
                                                    transition={{
                                                        duration: 1.5,
                                                        repeat: Infinity,
                                                    }}
                                                >
                                                    →
                                                </motion.span>
                                            </motion.button>
                                        </div>
                                    </div>
                                </Link>
                            </motion.div>
                        ))}
                    </div>

                    {/* Call to action */}
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1, duration: 0.8 }}
                        className="text-center mt-12"
                    >
                        <motion.button
                            whileHover={{ scale: 1.05, y: -3 }}
                            whileTap={{ scale: 0.95 }}
                            className="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            Read All Stories →
                        </motion.button>
                    </motion.div>
                </div>
            </section>

            {/* Gallery Section - CLEANED UP */}
            <section
                ref={galleryRef}
                className="min-h-screen relative overflow-hidden py-20 z-10"
            >
                <div className="absolute inset-0 backdrop-blur-sm" />

                <div className="container mx-auto px-6 relative z-10">
                    {/* Your existing gallery content here - just remove the style prop and transitions */}
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
                            Capturing moments and memories from {village?.name}
                        </motion.p>
                        <motion.div
                            initial={{ width: 0 }}
                            whileInView={{ width: "8rem" }}
                            transition={{ delay: 0.5, duration: 1 }}
                            className="h-1 bg-gradient-to-r from-pink-400 to-purple-400 mx-auto"
                        />
                    </motion.div>
                    {/* Artistic Grid Layout - Ryze Designs Inspired */}
                    <div className="grid grid-cols-12 gap-4 max-w-7xl mx-auto">
                        {gallery?.slice(0, 12).map((image, index) => {
                            // Create varied grid layouts for artistic effect
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
                                                transition={{ duration: 0.5 }}
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-white/50 relative">
                                                <motion.span
                                                    className="text-4xl"
                                                    animate={{
                                                        rotate: [0, 5, -5, 0],
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
                                            initial={{ scale: 0, rotate: -90 }}
                                            whileHover={{ scale: 1, rotate: 0 }}
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
                                            initial={{ y: "100%", opacity: 0 }}
                                            whileHover={{ y: 0, opacity: 1 }}
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
                                                    transition={{ delay: 0.1 }}
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
                                                    transition={{ delay: 0.2 }}
                                                    className="text-sm text-white font-medium line-clamp-2"
                                                >
                                                    {image.caption}
                                                </motion.div>
                                            )}

                                            <motion.div
                                                initial={{ x: -20, opacity: 0 }}
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

                    {/* Gallery stats with animated counters */}
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1.2, duration: 0.8 }}
                        className="grid grid-cols-3 gap-8 mt-16 max-w-2xl mx-auto"
                    >
                        {[
                            {
                                label: "Total Photos",
                                value: gallery?.length || 0,
                                icon: "📸",
                            },
                            {
                                label: "Locations",
                                value: new Set(
                                    gallery
                                        ?.map((img) => img.place?.id)
                                        .filter(Boolean)
                                ).size,
                                icon: "📍",
                            },
                            { label: "Memories", value: "∞", icon: "💫" },
                        ].map((stat, index) => (
                            <motion.div
                                key={stat.label}
                                initial={{ scale: 0, rotateY: 90 }}
                                whileInView={{ scale: 1, rotateY: 0 }}
                                transition={{
                                    delay: 1.5 + index * 0.2,
                                    duration: 0.6,
                                    type: "spring",
                                }}
                                whileHover={{ scale: 1.1, y: -5 }}
                                className="text-center bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/20"
                            >
                                <motion.div
                                    animate={{ rotate: [0, 10, -10, 0] }}
                                    transition={{
                                        duration: 2,
                                        repeat: Infinity,
                                        delay: index,
                                    }}
                                    className="text-3xl mb-2"
                                >
                                    {stat.icon}
                                </motion.div>
                                <div className="text-2xl font-bold text-white mb-1">
                                    {stat.value}
                                </div>
                                <div className="text-sm text-white/70">
                                    {stat.label}
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>

                    {/* Call to action */}
                    <motion.div
                        initial={{ opacity: 0, scale: 0.8 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 2, duration: 0.8 }}
                        className="text-center mt-12"
                    >
                        <motion.button
                            whileHover={{
                                scale: 1.05,
                                y: -3,
                                boxShadow: "0 20px 40px rgba(255,255,255,0.1)",
                            }}
                            whileTap={{ scale: 0.95 }}
                            className="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center mx-auto"
                        >
                            <span className="mr-2">🖼️</span>
                            View Full Gallery
                            <motion.span
                                animate={{ x: [0, 5, 0] }}
                                transition={{ duration: 1.5, repeat: Infinity }}
                                className="ml-2"
                            >
                                →
                            </motion.span>
                        </motion.button>
                    </motion.div>
                </div>
            </section>
        </MainLayout>
    );
};

export default VillageHomePage;
