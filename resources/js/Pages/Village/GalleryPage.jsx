import React, { useState, useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform, useSpring } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const GalleryPage = ({ village, images, places, filters }) => {
    const [filteredImages, setFilteredImages] = useState(images.data);
    const [selectedPlace, setSelectedPlace] = useState(filters.place || "");
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [selectedImage, setSelectedImage] = useState(null);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 500], [0, -150]);
    const overlayOpacity = useTransform(scrollY, [0, 300], [0.3, 0.7]);

    // Artistic scroll animations
    const geometryRotate = useTransform(scrollY, [0, 1000], [0, 360]);
    const geometryScale = useTransform(scrollY, [0, 1000], [1, 1.5]);
    const geometryOpacity = useTransform(scrollY, [200, 800], [0.2, 0.8]);

    // Village ambient music
    useEffect(() => {
        if (audioRef.current) {
            audioRef.current.volume = 0.2;
            audioRef.current.play().catch(console.log);
        }

        return () => {
            if (audioRef.current) {
                audioRef.current.pause();
            }
        };
    }, []);

    useEffect(() => {
        let filtered = images.data;

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
    }, [searchTerm, selectedPlace, images.data]);

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

    return (
        <MainLayout title="Gallery">
            <Head title={`Gallery - ${village?.name}`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/village-nature.mp3" type="audio/mpeg" />
            </audio>

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden">
                {/* Background with parallax */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 bg-gradient-to-b from-purple-600 via-pink-500 to-red-600"
                >
                    <div className="absolute inset-0 opacity-20">
                        <svg viewBox="0 0 1200 600" className="w-full h-full">
                            <path
                                d="M0,600 L0,200 Q300,150 600,180 T1200,160 L1200,600 Z"
                                fill="#4c1d95"
                            />
                            <path
                                d="M0,600 L0,300 Q400,250 800,270 T1200,250 L1200,600 Z"
                                fill="#6b21a8"
                            />
                            <path
                                d="M0,600 L0,400 Q500,350 1000,370 T1200,350 L1200,600 Z"
                                fill="#7c2d12"
                            />
                        </svg>
                    </div>
                </motion.div>

                {/* Artistic Geometric Elements */}
                <motion.div
                    style={{
                        rotate: geometryRotate,
                        scale: geometryScale,
                        opacity: geometryOpacity,
                    }}
                    className="absolute top-20 right-20 w-32 h-32 opacity-30"
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
                        <rect
                            x="35"
                            y="35"
                            width="30"
                            height="30"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="1"
                        />
                    </svg>
                </motion.div>

                <motion.div
                    style={{
                        rotate: geometryRotate,
                        scale: geometryScale,
                        opacity: geometryOpacity,
                    }}
                    className="absolute bottom-32 left-16 w-24 h-24 opacity-20"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <polygon
                            points="50,10 80,30 80,70 50,90 20,70 20,30"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                        />
                        <circle
                            cx="50"
                            cy="50"
                            r="15"
                            fill="currentColor"
                            opacity="0.4"
                        />
                    </svg>
                </motion.div>

                {/* Animated overlay */}
                <motion.div
                    style={{ opacity: overlayOpacity }}
                    className="absolute inset-0 bg-black"
                />

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                    <div className="max-w-4xl px-6">
                        <motion.h1
                            initial={{ opacity: 0, y: 50 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="text-6xl md:text-8xl font-bold text-white mb-6"
                        >
                            Village Gallery
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            Capturing moments and memories from {village?.name}
                        </motion.p>

                        {/* Filter Controls */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.5 }}
                            className="max-w-3xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            {/* Search */}
                            <div className="relative">
                                <input
                                    type="text"
                                    placeholder="Search gallery..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50"
                                />
                                <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg
                                        className="w-5 h-5 text-gray-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            {/* Place Filter */}
                            <select
                                value={selectedPlace}
                                onChange={(e) =>
                                    setSelectedPlace(e.target.value)
                                }
                                className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                            >
                                <option value="" className="text-black">
                                    All Places
                                </option>
                                {places?.map((place) => (
                                    <option
                                        key={place.id}
                                        value={place.id}
                                        className="text-black"
                                    >
                                        {place.name}
                                    </option>
                                ))}
                            </select>

                            {/* Clear Filters */}
                            <button
                                onClick={() => {
                                    setSearchTerm("");
                                    setSelectedPlace("");
                                }}
                                className="px-4 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-white hover:bg-white/30 transition-colors duration-300"
                            >
                                Clear Filters
                            </button>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Gallery Grid Section - Artistic Ryze Design Style */}
            <section className="py-20 bg-gradient-to-b from-red-600 to-purple-900 relative overflow-hidden">
                {/* Background Geometric Patterns */}
                <div className="absolute inset-0 opacity-10">
                    <svg viewBox="0 0 1200 800" className="w-full h-full">
                        <defs>
                            <pattern
                                id="gallery-grid"
                                width="50"
                                height="50"
                                patternUnits="userSpaceOnUse"
                            >
                                <path
                                    d="M 50 0 L 0 0 0 50"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1"
                                />
                            </pattern>
                        </defs>
                        <rect
                            width="1200"
                            height="800"
                            fill="url(#gallery-grid)"
                            className="text-white"
                        />
                    </svg>
                </div>

                <div className="container mx-auto px-6 relative z-10">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="mb-12"
                    >
                        <h2 className="text-4xl font-bold text-white text-center mb-4">
                            {filteredImages.length} Photo
                            {filteredImages.length !== 1 ? "s" : ""} Found
                        </h2>
                        <div className="w-24 h-1 bg-gradient-to-r from-pink-400 to-purple-500 mx-auto"></div>
                    </motion.div>

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
                    {images.last_page > 1 && <Pagination images={images} />}
                </div>
            </section>

            {/* Statistics Section */}
            <section className="py-20 bg-gradient-to-b from-purple-900 to-gray-900">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="grid grid-cols-2 md:grid-cols-4 gap-8"
                    >
                        <div className="text-center text-white">
                            <motion.div
                                className="text-4xl font-bold mb-2"
                                initial={{ opacity: 0, scale: 0.5 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.8, delay: 0.1 }}
                            >
                                {images.total || 0}
                            </motion.div>
                            <div className="text-gray-300">Total Photos</div>
                        </div>
                        <div className="text-center text-white">
                            <motion.div
                                className="text-4xl font-bold mb-2"
                                initial={{ opacity: 0, scale: 0.5 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.8, delay: 0.2 }}
                            >
                                {places?.length || 0}
                            </motion.div>
                            <div className="text-gray-300">Locations</div>
                        </div>
                        <div className="text-center text-white">
                            <motion.div
                                className="text-4xl font-bold mb-2"
                                initial={{ opacity: 0, scale: 0.5 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.8, delay: 0.3 }}
                            >
                                {
                                    filteredImages.filter((img) => img.caption)
                                        .length
                                }
                            </motion.div>
                            <div className="text-gray-300">With Captions</div>
                        </div>
                        <div className="text-center text-white">
                            <motion.div
                                className="text-4xl font-bold mb-2"
                                initial={{ opacity: 0, scale: 0.5 }}
                                whileInView={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                            >
                                {new Date().getFullYear()}
                            </motion.div>
                            <div className="text-gray-300">Current Year</div>
                        </div>
                    </motion.div>
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
                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
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
                        <div className="text-sm text-white line-clamp-2">
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
                    className="absolute top-4 right-4 z-10 text-white hover:text-gray-300 transition-colors"
                >
                    <svg
                        className="w-8 h-8"
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
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        onNavigate("prev");
                    }}
                    className="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors"
                >
                    <svg
                        className="w-8 h-8"
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
                    className="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors"
                >
                    <svg
                        className="w-8 h-8"
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
                        className="max-w-full max-h-[80vh] object-contain mx-auto rounded-lg"
                    />

                    {/* Image Info */}
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6 rounded-b-lg">
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

const Pagination = ({ images }) => {
    const { current_page, last_page } = images;

    return (
        <motion.div
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            transition={{ duration: 0.8 }}
            className="flex justify-center items-center mt-16 space-x-4"
        >
            {current_page > 1 && (
                <Link
                    href={`?page=${current_page - 1}`}
                    className="px-6 py-3 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-colors duration-300"
                >
                    ← Previous
                </Link>
            )}

            <div className="flex items-center space-x-2">
                {Array.from({ length: Math.min(5, last_page) }, (_, i) => {
                    const page = i + Math.max(1, current_page - 2);
                    if (page > last_page) return null;

                    return (
                        <Link
                            key={page}
                            href={`?page=${page}`}
                            className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 ${
                                page === current_page
                                    ? "bg-purple-500 text-white"
                                    : "bg-white/10 text-gray-300 hover:bg-white/20"
                            }`}
                        >
                            {page}
                        </Link>
                    );
                })}
            </div>

            {current_page < last_page && (
                <Link
                    href={`?page=${current_page + 1}`}
                    className="px-6 py-3 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-colors duration-300"
                >
                    Next →
                </Link>
            )}
        </motion.div>
    );
};

export default GalleryPage;
