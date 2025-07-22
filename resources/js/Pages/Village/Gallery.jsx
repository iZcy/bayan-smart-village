// resources/js/Pages/Village/Gallery.jsx (Updated with Media)
import React, { useState, useEffect, useRef } from "react";
import { Head } from "@inertiajs/react";
import {
    motion,
    AnimatePresence,
    useScroll,
    useTransform,
} from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import MediaBackground from "@/Components/MediaBackground";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import Pagination from "@/Components/Pagination";
import SlideshowBackground from "@/Components/SlideshowBackground";
import { useSlideshowData, slideshowConfigs } from "@/hooks/useSlideshowData";

const GalleryPage = ({ village, images, places = [], filters = {} }) => {
    // Ensure we have valid data
    const imageData = images?.data || [];
    const filterData = filters || {};
    const placeData = places || [];

    const [filteredImages, setFilteredImages] = useState(imageData);
    const [selectedPlace, setSelectedPlace] = useState(filterData.place || "");
    const [searchTerm, setSearchTerm] = useState(filterData.search || "");
    const [selectedImage, setSelectedImage] = useState(null);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const [contextAudio, setContextAudio] = useState(null);
    const [isContextAudioPlaying, setIsContextAudioPlaying] = useState(false);
    const contextAudioRef = useRef(null);
    const { scrollY } = useScroll();

    // Prepare slideshow data using the custom hook
    const slideshowImages = useSlideshowData(imageData, slideshowConfigs.gallery);

    // Color overlay based on scroll for Gallery sections
    const colorOverlay = useTransform(
        scrollY,
        [0, 800, 1600, 2400, 3200],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.2))", // Hero
            "linear-gradient(to bottom, rgba(219,39,119,0.4), rgba(126,34,206,0.6))", // Gallery Grid - pink to purple
            "linear-gradient(to bottom, rgba(126,34,206,0.4), rgba(88,28,135,0.6))", // Statistics - purple to darker purple
            "linear-gradient(to bottom, rgba(88,28,135,0.4), rgba(55,48,163,0.6))", // Categories - dark purple to indigo
            "linear-gradient(to bottom, rgba(55,48,163,0.3), rgba(0,0,0,0.4))", // End fade
        ]
    );

    // Fetch context-specific audio
    useEffect(() => {
        const fetchContextAudio = async () => {
            try {
                const response = await fetch(
                    "/api/media/gallery/featured?type=audio"
                );
                if (response.ok) {
                    const data = await response.json();
                    if (data.media) {
                        setContextAudio(data.media);
                    }
                }
            } catch (error) {
                console.log("Failed to fetch gallery audio:", error);
            }
        };

        fetchContextAudio();
    }, [village]);

    // Handle context audio
    useEffect(() => {
        if (contextAudioRef.current && contextAudio) {
            contextAudioRef.current.volume = contextAudio.volume || 0.2;
            if (contextAudio.autoplay) {
                contextAudioRef.current.play().catch(console.log);
                setIsContextAudioPlaying(true);
            }
        }
        return () => {
            if (contextAudioRef.current) {
                contextAudioRef.current.pause();
            }
        };
    }, [contextAudio]);

    const toggleContextAudio = () => {
        if (contextAudioRef.current) {
            if (isContextAudioPlaying) {
                contextAudioRef.current.pause();
                setIsContextAudioPlaying(false);
            } else {
                contextAudioRef.current.play().catch(console.log);
                setIsContextAudioPlaying(true);
            }
        }
    };

    useEffect(() => {
        let filtered = imageData;

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(
                (image) =>
                    image.caption
                        ?.toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    image.place?.name
                        ?.toLowerCase()
                        .includes(searchTerm.toLowerCase())
            );
        }

        // Filter by place
        if (selectedPlace) {
            filtered = filtered.filter(
                (image) => image.place?.id === selectedPlace
            );
        }

        setFilteredImages(filtered);
    }, [searchTerm, selectedPlace, imageData]);

    const openLightbox = (image) => {
        setSelectedImage(image);
        setIsLightboxOpen(true);
    };

    const closeLightbox = () => {
        setSelectedImage(null);
        setIsLightboxOpen(false);
    };

    const navigateImage = (direction) => {
        const currentIndex = filteredImages.findIndex(
            (img) => img.id === selectedImage.id
        );
        let newIndex;

        if (direction === "next") {
            newIndex = (currentIndex + 1) % filteredImages.length;
        } else {
            newIndex =
                currentIndex === 0
                    ? filteredImages.length - 1
                    : currentIndex - 1;
        }

        setSelectedImage(filteredImages[newIndex]);
    };

    // Place filter component
    const placeFilterComponent = (
        <select
            value={selectedPlace}
            onChange={(e) => setSelectedPlace(e.target.value)}
            className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
        >
            <option value="" className="text-black">
                All Places
            </option>
            {placeData?.map((place) => (
                <option key={place.id} value={place.id} className="text-black">
                    {place.name}
                </option>
            ))}
        </select>
    );

    const handleClearFilters = () => {
        setSearchTerm("");
        setSelectedPlace("");
    };

    return (
        <MainLayout title="Gallery">
            <Head title={`Gallery - ${village?.name}`} />
            {/* Audio-only Media Background */}
            <MediaBackground
                context="gallery"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                controlsId="gallery-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/village-nature.mp3"
            />
            {/* Context Audio */}
            {contextAudio && (
                <audio
                    ref={contextAudioRef}
                    loop={contextAudio.loop}
                    preload="auto"
                >
                    <source src={contextAudio.file_url} type="audio/mpeg" />
                </audio>
            )}

            {/* Enhanced Color Overlay */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{ background: colorOverlay }}
            />

            {/* Slideshow Background */}
            <SlideshowBackground
                images={slideshowImages}
                interval={slideshowConfigs.gallery.interval}
                transitionDuration={slideshowConfigs.gallery.transitionDuration}
                placeholderConfig={slideshowConfigs.gallery.placeholderConfig}
            />
            {/* Audio Control for Gallery */}
            {contextAudio && (
                <motion.button
                    onClick={toggleContextAudio}
                    className="fixed top-20 right-6 z-[60] bg-black/20 backdrop-blur-md text-white p-3 rounded-full hover:bg-black/30 transition-colors"
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                    title={contextAudio.title || "Gallery Audio"}
                >
                    {isContextAudioPlaying ? "🔊" : "🔇"}
                </motion.button>
            )}
            {/* Hero Section */}
            <HeroSection
                title="Village Gallery"
                subtitle={`Capturing moments and memories from ${village?.name}`}
                backgroundGradient="from-transparent to-transparent"
                parallax={true}
                scrollY={{ useTransform: (scrollY) => scrollY }}
            >
                <FilterControls
                    searchTerm={searchTerm}
                    setSearchTerm={setSearchTerm}
                    selectedCategory={selectedPlace}
                    setSelectedCategory={setSelectedPlace}
                    categories={placeData}
                    additionalFilters={[{ component: placeFilterComponent }]}
                    searchPlaceholder="Search gallery..."
                    className="max-w-3xl mx-auto"
                />
            </HeroSection>
            {/* Gallery Grid Section */}
            <section className="py-20 relative overflow-hidden">
                {/* Background blur overlay */}
                <div className="absolute inset-0 backdrop-blur-sm" />
                <div className="container mx-auto px-6 relative z-10">
                    <SectionHeader
                        title="Photo"
                        count={filteredImages.length}
                        gradientColor="from-pink-400 to-purple-500"
                    />

                    {/* Artistic Grid Layout */}
                    <div className="grid grid-cols-12 gap-4 max-w-7xl mx-auto">
                        {filteredImages.map((image, index) => (
                            <GalleryItem
                                key={image.id}
                                image={image}
                                index={index}
                                onClick={() => openLightbox(image)}
                            />
                        ))}
                    </div>

                    {filteredImages.length === 0 && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            className="text-center py-20"
                        >
                            <div className="text-6xl mb-4">📸</div>
                            <h3 className="text-2xl font-semibold text-white mb-2">
                                No Photos Found
                            </h3>
                            <p className="text-gray-400">
                                Try adjusting your search or filters
                            </p>
                        </motion.div>
                    )}

                    {/* Pagination */}
                    {images?.last_page > 1 && (
                        <Pagination paginationData={images} theme="gallery" />
                    )}
                </div>
            </section>

            {/* Lightbox Modal */}
            {isLightboxOpen && selectedImage && (
                <LightboxModal
                    image={selectedImage}
                    onClose={closeLightbox}
                    onNavigate={navigateImage}
                    currentIndex={filteredImages.findIndex(
                        (img) => img.id === selectedImage.id
                    )}
                    totalImages={filteredImages.length}
                />
            )}
        </MainLayout>
    );
};

// Gallery Item Component (unchanged but included for completeness)
const GalleryItem = ({ image, index, onClick }) => {
    const [ref, inView] = useInView({
        threshold: 0.1,
        triggerOnce: true,
    });

    // Create varied grid layouts for artistic effect
    const getGridSpan = () => {
        const patterns = [
            "col-span-6 row-span-2", // Large
            "col-span-4 row-span-1", // Medium
            "col-span-3 row-span-1", // Small
            "col-span-4 row-span-2", // Tall
            "col-span-5 row-span-1", // Wide
        ];
        return patterns[index % patterns.length];
    };

    const getAspectRatio = () => {
        const ratios = [
            "aspect-video",
            "aspect-square",
            "aspect-[4/3]",
            "aspect-[3/4]",
            "aspect-[16/10]",
        ];
        return ratios[index % ratios.length];
    };

    return (
        <motion.div
            ref={ref}
            initial={{ opacity: 0, scale: 0.8, rotateY: 45 }}
            animate={inView ? { opacity: 1, scale: 1, rotateY: 0 } : {}}
            transition={{
                duration: 0.8,
                delay: (index % 12) * 0.1,
                type: "spring",
                stiffness: 100,
            }}
            whileHover={{
                scale: 1.05,
                rotateY: 5,
                zIndex: 20,
                transition: { duration: 0.3 },
            }}
            className={`${getGridSpan()} cursor-pointer group relative overflow-hidden rounded-2xl`}
            onClick={onClick}
        >
            <div
                className={`w-full ${getAspectRatio()} bg-gradient-to-br from-purple-500 to-pink-600 relative overflow-hidden`}
            >
                {image.image_url ? (
                    <img
                        src={image.image_url}
                        alt={image.caption || "Gallery image"}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-white/50">
                        <span className="text-4xl">🖼️</span>
                    </div>
                )}

                {/* Overlay */}
                <div className="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <div className="text-white text-center p-4">
                        <div className="text-2xl mb-2">🔍</div>
                        <div className="text-sm font-semibold">View Image</div>
                    </div>
                </div>

                {/* Image Info */}
                <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    {image.place && (
                        <div className="text-xs text-purple-300 mb-1">
                            📍 {image.place.name}
                        </div>
                    )}
                    {image.caption && (
                        <div className="text-sm text-white line-clamp-3 leading-relaxed h-[68px] overflow-hidden">
                            {image.caption}
                        </div>
                    )}
                    <div className="text-xs text-gray-400 mt-2">
                        {new Date(image.created_at).toLocaleDateString()}
                    </div>
                </div>
            </div>
        </motion.div>
    );
};

// Lightbox Modal Component (unchanged but included for completeness)
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

export default GalleryPage;
