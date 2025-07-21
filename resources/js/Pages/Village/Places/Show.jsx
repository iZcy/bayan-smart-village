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
import MediaBackground from "@/Components/MediaBackground";
import { BaseCard } from "@/Components/Cards/Index";

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
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
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
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                onNavigate("next");
                            }}
                            className="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black/50 rounded-full"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
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
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 rounded-b-lg">
                        <div className="flex items-center justify-between text-white">
                            <div>
                                {image.caption && (
                                    <div className="text-lg font-semibold mb-1">
                                        {image.caption}
                                    </div>
                                )}
                                <div className="text-sm text-gray-400">
                                    {new Date(image.created_at).toLocaleDateString("en-US", {
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

export default function PlaceShowPage({ village, place, placeContext }) {
    const { scrollY } = useScroll();
    const audioRef = useRef(null);
    
    // Gallery lightbox state
    const [selectedImage, setSelectedImage] = useState(null);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

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

    // Gallery functions
    const openLightbox = (image) => {
        setSelectedImage(image);
        setIsLightboxOpen(true);
    };

    const closeLightbox = () => {
        setSelectedImage(null);
        setIsLightboxOpen(false);
    };

    const navigateImage = (direction) => {
        if (!place.images || place.images.length === 0) return;
        
        const currentIndex = place.images.findIndex(img => img.id === selectedImage.id);
        let newIndex;
        
        if (direction === "prev") {
            newIndex = currentIndex === 0 ? place.images.length - 1 : currentIndex - 1;
        } else {
            newIndex = currentIndex === place.images.length - 1 ? 0 : currentIndex + 1;
        }
        
        setSelectedImage(place.images[newIndex]);
    };

    return (
        <MainLayout title={place.name} description={place.description}>
            <Head title={`${place.name} - ${village.name}`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/village-ambient.mp3" type="audio/mpeg" />
            </audio>

            {/* Media Background */}
            <MediaBackground
                context="place"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                controlsId="place-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Place Image Background Overlay */}
            <div
                className="fixed inset-0 bg-cover bg-center z-0"
                style={{
                    backgroundImage: place.image_url
                        ? `url(${place.image_url})`
                        : "none",
                }}
            >
                <div className="absolute inset-0 bg-black/50" />
            </div>

            {/* Hero Section */}
            <motion.section
                className="relative min-h-screen flex items-center justify-center"
                style={{ y: heroY, opacity: heroOpacity }}
            >
                <div className="relative z-10 text-center max-w-5xl mx-auto px-6 py-20">
                    {/* Breadcrumb */}
                    <motion.nav 
                        className="mb-8"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.2 }}
                    >
                        <div className="flex items-center justify-center space-x-2 text-white/70">
                            <Link href="/" className="hover:text-white transition-colors">
                                {village.name}
                            </Link>
                            <span>/</span>
                            <Link href="/places" className="hover:text-white transition-colors">
                                Places
                            </Link>
                            <span>/</span>
                            <span className="text-white">{place.name}</span>
                        </div>
                    </motion.nav>

                    {/* Place Title */}
                    <motion.h1
                        className="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-white via-green-200 to-blue-200 bg-clip-text text-transparent"
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 1, delay: 0.5 }}
                    >
                        {place.name}
                    </motion.h1>

                    {/* Place Meta */}
                    <motion.div
                        className="flex flex-wrap items-center justify-center gap-4 mb-8"
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.7 }}
                    >
                        {place.category && (
                            <span className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 rounded-full text-white font-semibold text-lg">
                                {placeContext.is_service_place && "🏛️"} 
                                {placeContext.is_product_place && "🏪"} 
                                {place.category.name}
                            </span>
                        )}
                        <span className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white">
                            📍 {village.name}
                        </span>
                        {placeContext.business_count > 0 && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white">
                                🏢 {placeContext.business_count} {placeContext.business_count === 1 ? 'Business' : 'Businesses'}
                            </span>
                        )}
                    </motion.div>

                    {/* Description */}
                    <motion.p
                        className="text-lg md:text-xl text-white/90 max-w-3xl mx-auto leading-relaxed mb-12"
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.9 }}
                    >
                        {place.description}
                    </motion.p>

                    {/* Action Buttons */}
                    <motion.div
                        className="flex flex-col sm:flex-row gap-4 items-center justify-center"
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 1.1 }}
                    >
                        <button
                            onClick={() => {
                                document.getElementById("details").scrollIntoView({ behavior: "smooth" });
                            }}
                            className="px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300 border border-white/30 font-semibold"
                        >
                            Explore Details
                        </button>
                        {place.latitude && place.longitude && (
                            <button
                                onClick={() => {
                                    document.getElementById("location").scrollIntoView({ behavior: "smooth" });
                                }}
                                className="px-8 py-4 bg-blue-600/80 backdrop-blur-md text-white rounded-full hover:bg-blue-600 transition-all duration-300 font-semibold"
                            >
                                View Location
                            </button>
                        )}
                    </motion.div>
                </div>

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

            {/* Main Content */}
            <div className="relative bg-white">
                {/* Place Details Section */}
                <section id="details" className="py-20">
                    <div className="container mx-auto px-6">
                        <motion.div
                            ref={contentRef}
                            initial={{ opacity: 0, y: 50 }}
                            animate={contentInView ? { opacity: 1, y: 0 } : {}}
                            transition={{ duration: 1 }}
                            className="max-w-6xl mx-auto"
                        >
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
                                {/* Place Information */}
                                <div className="lg:col-span-2 space-y-8">
                                    {/* Contact & Location Info */}
                                    {(place.address || place.phone_number) && (
                                        <div className="bg-gradient-to-br from-blue-50 to-green-50 rounded-2xl p-8">
                                            <h3 className="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                                <span className="text-3xl mr-3">📞</span>
                                                Contact & Location
                                            </h3>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {place.phone_number && (
                                                    <div>
                                                        <h4 className="font-semibold text-gray-700 mb-2">Phone</h4>
                                                        <a
                                                            href={`tel:${place.phone_number}`}
                                                            className="text-blue-600 hover:text-blue-700 transition-colors font-medium text-lg"
                                                        >
                                                            {place.phone_number}
                                                        </a>
                                                    </div>
                                                )}
                                                {place.address && (
                                                    <div>
                                                        <h4 className="font-semibold text-gray-700 mb-2">Address</h4>
                                                        <p className="text-gray-600 leading-relaxed">{place.address}</p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {/* Custom Fields - Dynamic based on place type */}
                                    {place.custom_fields && Object.keys(place.custom_fields).length > 0 && (
                                        <div className="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-2xl p-8">
                                            <h3 className="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                                <span className="text-3xl mr-3">ℹ️</span>
                                                {placeContext.is_service_place ? "Visitor Information" : "Details"}
                                            </h3>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {Object.entries(place.custom_fields).map(([key, value]) => (
                                                    <div key={key} className="bg-white/50 rounded-lg p-4">
                                                        <h4 className="font-semibold text-gray-700 capitalize mb-2">
                                                            {key.replace(/_/g, " ")}
                                                        </h4>
                                                        <p className="text-gray-800">{value}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {/* Statistics */}
                                    {(placeContext.business_count > 0 || placeContext.product_count > 0) && (
                                        <div className="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8">
                                            <h3 className="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                                <span className="text-3xl mr-3">📊</span>
                                                What's Here
                                            </h3>
                                            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                                                <div className="text-center">
                                                    <div className="text-3xl font-bold text-purple-600">{placeContext.business_count}</div>
                                                    <div className="text-gray-600 font-medium">Businesses</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-3xl font-bold text-pink-600">{placeContext.product_count}</div>
                                                    <div className="text-gray-600 font-medium">Products</div>
                                                </div>
                                                {place.images && (
                                                    <div className="text-center">
                                                        <div className="text-3xl font-bold text-green-600">{place.images.length}</div>
                                                        <div className="text-gray-600 font-medium">Photos</div>
                                                    </div>
                                                )}
                                                {place.articles && (
                                                    <div className="text-center">
                                                        <div className="text-3xl font-bold text-blue-600">{place.articles.length}</div>
                                                        <div className="text-gray-600 font-medium">Stories</div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Map Section */}
                                {place.latitude && place.longitude && (
                                    <div id="location" className="lg:sticky lg:top-20 self-start">
                                        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                                            <div className="p-6 bg-gradient-to-r from-blue-600 to-purple-600">
                                                <h3 className="text-xl font-bold text-white mb-2 flex items-center">
                                                    <span className="text-2xl mr-2">🗺️</span>
                                                    Location Map
                                                </h3>
                                                <p className="text-blue-100 text-sm">
                                                    {place.latitude}, {place.longitude}
                                                </p>
                                            </div>
                                            <div className="aspect-square">
                                                <iframe
                                                    src={`https://maps.google.com/maps?q=${place.latitude},${place.longitude}&z=15&output=embed`}
                                                    width="100%"
                                                    height="100%"
                                                    style={{ border: 0 }}
                                                    loading="lazy"
                                                    referrerPolicy="no-referrer-when-downgrade"
                                                />
                                            </div>
                                            <div className="p-4">
                                                <a
                                                    href={`https://www.google.com/maps/search/?api=1&query=${place.latitude},${place.longitude}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                                                >
                                                    Open in Google Maps
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </motion.div>
                    </div>
                </section>

                {/* Gallery Section */}
                {place.images && place.images.length > 0 && (
                    <section className="py-20 bg-gray-50">
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl font-bold text-center mb-16 text-gray-900"
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 1 }}
                                viewport={{ once: true }}
                            >
                                Photo Gallery
                            </motion.h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 max-w-7xl mx-auto">
                                {place.images.map((image, index) => (
                                    <motion.div
                                        key={image.id}
                                        className="relative group cursor-pointer overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                                        initial={{ opacity: 0, scale: 0.9 }}
                                        whileInView={{ opacity: 1, scale: 1 }}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        whileHover={{ y: -5 }}
                                        onClick={() => openLightbox(image)}
                                        viewport={{ once: true }}
                                    >
                                        <img
                                            src={image.image_url}
                                            alt={image.caption || `Gallery image ${index + 1}`}
                                            className="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300"
                                        />
                                        <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <div className="text-white text-center">
                                                <svg className="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <p className="text-sm font-medium">View Full Size</p>
                                            </div>
                                        </div>
                                        {image.caption && (
                                            <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                                <p className="text-white text-sm font-medium">{image.caption}</p>
                                            </div>
                                        )}
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </section>
                )}

                {/* SMEs Section */}
                {place.smes && place.smes.length > 0 && (
                    <section className="py-20">
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl font-bold text-center mb-16 text-gray-900"
                                initial={{ opacity: 0, y: 30 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8 }}
                            >
                                Businesses at {place.name}
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
                                        <Link href={`/products?sme=${sme.slug}`}>
                                            <BaseCard
                                                className="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200"
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
                                                            <span className="text-4xl text-white">🏪</span>
                                                        </div>
                                                    )}
                                                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                                </div>
                                                <div className="p-6">
                                                    <h3 className="text-xl font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">
                                                        {sme.name}
                                                    </h3>
                                                    <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                                                        {sme.description}
                                                    </p>
                                                    <div className="flex items-center justify-between">
                                                        {sme.contact_phone && (
                                                            <div className="flex items-center text-sm text-gray-500">
                                                                <span className="mr-1">📞</span>
                                                                <span>{sme.contact_phone}</span>
                                                            </div>
                                                        )}
                                                        {sme.offers && sme.offers.length > 0 && (
                                                            <span className="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                                                {sme.offers.length} Products
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </BaseCard>
                                        </Link>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </section>
                )}

                {/* Articles Section */}
                {place.articles && place.articles.length > 0 && (
                    <section className="py-20 bg-gray-50">
                        <div className="container mx-auto px-6">
                            <motion.h2
                                className="text-4xl font-bold text-center mb-16 text-gray-900"
                                initial={{ opacity: 0, y: 30 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.8 }}
                            >
                                Stories from {place.name}
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
                                        <Link href={`/articles/${article.slug}`}>
                                            <BaseCard
                                                className="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200"
                                                hoverEffects={true}
                                            >
                                                <div className="relative h-48 overflow-hidden">
                                                    {article.cover_image_url ? (
                                                        <img
                                                            src={article.cover_image_url}
                                                            alt={article.title}
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                            <span className="text-4xl text-white">📖</span>
                                                        </div>
                                                    )}
                                                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                                </div>
                                                <div className="p-6">
                                                    <h3 className="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                                                        {article.title}
                                                    </h3>
                                                    <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                                                        {article.content?.replace(/<[^>]*>/g, "").substring(0, 120)}...
                                                    </p>
                                                    <div className="text-xs text-gray-500">
                                                        {new Date(article.published_at || article.created_at).toLocaleDateString()}
                                                    </div>
                                                </div>
                                            </BaseCard>
                                        </Link>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </section>
                )}
            </div>

            {/* Lightbox Modal */}
            <AnimatePresence>
                {isLightboxOpen && selectedImage && (
                    <LightboxModal
                        image={selectedImage}
                        onClose={closeLightbox}
                        onNavigate={navigateImage}
                        currentIndex={place.images?.findIndex(img => img.id === selectedImage.id) || 0}
                        totalImages={place.images?.length || 0}
                    />
                )}
            </AnimatePresence>
        </MainLayout>
    );
}