// resources/js/Pages/Village/Products/Show.jsx
import React, { useEffect, useRef, useState } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform, AnimatePresence } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import MediaBackground from "@/Components/MediaBackground";
import HeroSection from "@/Components/HeroSection";
import { ProductCard, ArticleCard } from "@/Components/Cards/Index";

const ProductShowPage = ({ village, product, relatedProducts, relatedStories }) => {
    const { scrollY } = useScroll();

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    // Image gallery state
    const [currentImageIndex, setCurrentImageIndex] = useState(0);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const [isHovering, setIsHovering] = useState(false);
    const slideshowIntervalRef = useRef(null);

    // Prepare all product images (primary + additional)
    const allImages = [
        ...(product.primary_image_url ? [{ 
            image_url: product.primary_image_url, 
            caption: product.name,
            is_primary: true 
        }] : []),
        ...(product.images || []).map(img => ({ 
            image_url: img.image_url, 
            caption: img.caption || `${product.name} - Image`,
            is_primary: false 
        }))
    ];

    // Start slideshow
    const startSlideshow = () => {
        if (allImages.length > 1 && !isLightboxOpen && !isHovering) {
            if (slideshowIntervalRef.current) {
                clearInterval(slideshowIntervalRef.current);
            }
            slideshowIntervalRef.current = setInterval(() => {
                setCurrentImageIndex((prevIndex) => 
                    (prevIndex + 1) % allImages.length
                );
            }, 5000);
        }
    };

    // Stop slideshow
    const stopSlideshow = () => {
        if (slideshowIntervalRef.current) {
            clearInterval(slideshowIntervalRef.current);
            slideshowIntervalRef.current = null;
        }
    };

    // Auto slideshow effect - controlled by hover and lightbox state
    useEffect(() => {
        if (allImages.length > 1 && !isLightboxOpen && !isHovering) {
            startSlideshow();
        } else {
            stopSlideshow();
        }

        return () => stopSlideshow();
    }, [allImages.length, isHovering, isLightboxOpen]);

    // Image navigation functions
    const openLightbox = (index = currentImageIndex) => {
        setCurrentImageIndex(index);
        setIsLightboxOpen(true);
        // Slideshow will be automatically stopped by useEffect
    };

    const closeLightbox = () => {
        setIsLightboxOpen(false);
        // Slideshow will be automatically restarted by useEffect
    };

    const navigateImage = (direction) => {
        if (direction === "next") {
            setCurrentImageIndex((prevIndex) => 
                (prevIndex + 1) % allImages.length
            );
        } else {
            setCurrentImageIndex((prevIndex) => 
                prevIndex === 0 ? allImages.length - 1 : prevIndex - 1
            );
        }
    };

    // Manual navigation (resets slideshow timer)
    const handleManualNavigation = (direction) => {
        navigateImage(direction);
        // Slideshow will be automatically restarted by useEffect with fresh 5-second timer
        if (!isLightboxOpen && !isHovering) {
            startSlideshow();
        }
    };

    // Utility function to format platform names properly
    const formatPlatformName = (platform) => {
        const platformMap = {
            'tokopedia': 'Tokopedia',
            'shopee': 'Shopee',
            'instagram': 'Instagram',
            'whatsapp': 'WhatsApp',
            'tiktok': 'TikTok',
            'facebook': 'Facebook',
            'twitter': 'Twitter',
            'youtube': 'YouTube',
            'website': 'Website',
            'email': 'Email'
        };
        return platformMap[platform?.toLowerCase()] || 
               platform?.charAt(0).toUpperCase() + platform?.slice(1).toLowerCase() || 
               platform;
    };

    const getDisplayPrice = () => {
        if (product.price) {
            return `Rp ${new Intl.NumberFormat("id-ID").format(product.price)}`;
        }
        if (product.price_range_min && product.price_range_max) {
            return `Rp ${new Intl.NumberFormat("id-ID").format(
                product.price_range_min
            )} - ${new Intl.NumberFormat("id-ID").format(
                product.price_range_max
            )}`;
        }
        if (product.price_range_min) {
            return `From Rp ${new Intl.NumberFormat("id-ID").format(
                product.price_range_min
            )}`;
        }
        return "Contact for price";
    };

    const handleLinkClick = async (link) => {
        try {
            // If no product_url, fallback to direct navigation
            if (!link.product_url) {
                console.warn('No product URL found for link:', link.platform);
                return;
            }

            // Track the click
            const response = await fetch(
                `/products/${product.id}/links/${link.id}/click`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    },
                }
            );

            if (response.ok) {
                const data = await response.json();
                window.open(data.redirect_url, "_blank");
            } else {
                // Fallback: open the URL directly if tracking fails
                window.open(link.product_url, "_blank");
            }
        } catch (error) {
            console.error("Error tracking link click:", error);
            // Fallback: open the URL directly if tracking fails
            if (link.product_url) {
                window.open(link.product_url, "_blank");
            }
        }
    };

    return (
        <MainLayout title={product.name}>
            <Head title={`${product.name} - ${village?.name}`} />

            {/* Media Background */}
            <MediaBackground
                context="products"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                disableAudio={true}
                controlsId="product-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Product Image Background Overlay with darken layer*/}
            <div
                className="fixed inset-0 bg-cover bg-center z-0"
                style={{
                    backgroundImage: product.primary_image_url
                        ? `url(${product.primary_image_url})`
                        : "none",
                }}
            >
                <div className="absolute inset-0 bg-black/50" />
            </div>

            {/* Hero Section */}
            <HeroSection
                title={product.name}
                subtitle={
                    product.short_description ||
                    "Quality product from our local artisans"
                }
                backgroundGradient="from-transparent to-transparent"
                parallax={true}
                scrollY={{ useTransform: useTransform }}
            >
                {/* Product meta in hero - Better Layout */}
                <motion.div
                    initial={{ opacity: 0, y: 50 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 1, delay: 1.5 }}
                    className="flex flex-col items-center gap-6 mt-8 max-w-2xl mx-auto"
                >
                    {/* Tags Row */}
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        {product.category && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/30">
                                📦 {product.category.name}
                            </span>
                        )}
                        {product.sme && (
                            <span className="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/30">
                                🏪 {product.sme.name}
                            </span>
                        )}
                    </div>

                    {/* Stock and Price Row */}
                    <div className="flex flex-col sm:flex-row items-center gap-4">
                        <span
                            className={`px-4 py-2 rounded-full text-sm font-medium text-white ${
                                product.availability === "available"
                                    ? "bg-green-500"
                                    : product.availability === "out_of_stock"
                                    ? "bg-red-500"
                                    : product.availability === "seasonal"
                                    ? "bg-yellow-500"
                                    : "bg-blue-500"
                            }`}
                        >
                            {product.availability
                                ?.replaceAll("_", " ")
                                .toUpperCase()}
                        </span>

                        <div className="text-2xl sm:text-3xl font-bold text-green-300 text-center">
                            {getDisplayPrice()}
                            {product.price_unit && (
                                <span className="text-base text-white/70 ml-2 block sm:inline">
                                    / {product.price_unit}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* View Details Button - Article Style */}
                    <motion.button
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.8, delay: 2.2 }}
                        onClick={() => {
                            document
                                .getElementById("content")
                                .scrollIntoView({ behavior: "smooth" });
                        }}
                        className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300 border border-white/30"
                    >
                        View Details
                        <svg
                            className="w-5 h-5 ml-2 group-hover:translate-y-1 transition-transform duration-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"
                            />
                        </svg>
                    </motion.button>
                </motion.div>
            </HeroSection>

            {/* Product Content */}
            <section id="content" className="relative py-20 bg-white">
                <div className="container mx-auto px-6 relative z-10">
                    <motion.div
                        ref={contentRef}
                        initial={{ opacity: 0, y: 50 }}
                        animate={contentInView ? { opacity: 1, y: 0 } : {}}
                        transition={{ duration: 1 }}
                        className="max-w-6xl mx-auto"
                    >
                        {/* Product Navigation */}
                        <nav className="mb-12">
                            <Link
                                href="/products"
                                className="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-300"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
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
                                Back to Products
                            </Link>
                        </nav>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
                            {/* Product Images */}
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                animate={contentInView ? { opacity: 1, x: 0 } : {}}
                                transition={{ duration: 0.8, delay: 0.2 }}
                            >
                                <div className="sticky top-20">
                                    {/* Main Image Display */}
                                    <div className="aspect-square bg-gray-100 rounded-2xl overflow-hidden mb-4 relative">
                                        {allImages.length > 0 ? (
                                            <div 
                                                className="w-full h-full cursor-pointer group"
                                                onClick={() => openLightbox(currentImageIndex)}
                                                onMouseEnter={() => setIsHovering(true)}
                                                onMouseLeave={() => setIsHovering(false)}
                                            >
                                                {/* Main Image with AnimatePresence */}
                                                <div className="relative w-full h-full overflow-hidden">
                                                    <AnimatePresence mode="wait">
                                                        <motion.img
                                                            key={currentImageIndex}
                                                            src={allImages[currentImageIndex].image_url}
                                                            alt={allImages[currentImageIndex].caption}
                                                            className="w-full h-full object-cover"
                                                            initial={{ opacity: 0, scale: 1.1 }}
                                                            animate={{ opacity: 1, scale: 1 }}
                                                            exit={{ opacity: 0, scale: 0.95 }}
                                                            transition={{ 
                                                                duration: 0.6, 
                                                                ease: [0.4, 0, 0.2, 1]
                                                            }}
                                                            whileHover={{ 
                                                                scale: 1.05,
                                                                transition: { duration: 0.5, ease: "easeOut" }
                                                            }}
                                                        />
                                                    </AnimatePresence>
                                                </div>
                                                
                                                {/* Animated Hover Overlay */}
                                                <motion.div 
                                                    className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent flex items-center justify-center"
                                                    initial={{ opacity: 0 }}
                                                    whileHover={{ opacity: 1 }}
                                                    transition={{ duration: 0.3, ease: "easeInOut" }}
                                                >
                                                    <motion.div 
                                                        className="text-white text-center"
                                                        initial={{ y: 20, opacity: 0 }}
                                                        whileHover={{ y: 0, opacity: 1 }}
                                                        transition={{ duration: 0.3, ease: "easeOut", delay: 0.1 }}
                                                    >
                                                        <motion.div 
                                                            className="text-2xl mb-1"
                                                            whileHover={{ scale: 1.1 }}
                                                            transition={{ duration: 0.2 }}
                                                        >
                                                            🔍
                                                        </motion.div>
                                                        <div className="text-sm font-medium">Click to enlarge</div>
                                                    </motion.div>
                                                </motion.div>

                                                {/* Animated Navigation Controls */}
                                                {allImages.length > 1 && (
                                                    <>
                                                        {/* Previous Button */}
                                                        <motion.button
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                handleManualNavigation("prev");
                                                            }}
                                                            className="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700"
                                                            initial={{ opacity: 0, x: -10 }}
                                                            animate={{ 
                                                                opacity: isHovering ? 1 : 0,
                                                                x: isHovering ? 0 : -10
                                                            }}
                                                            whileHover={{ 
                                                                scale: 1.1,
                                                                backgroundColor: "rgba(255, 255, 255, 1)",
                                                                boxShadow: "0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)"
                                                            }}
                                                            transition={{ duration: 0.2, ease: "easeOut" }}
                                                            whileTap={{ scale: 0.95 }}
                                                        >
                                                            <motion.svg 
                                                                className="w-5 h-5" 
                                                                fill="none" 
                                                                stroke="currentColor" 
                                                                viewBox="0 0 24 24"
                                                                whileHover={{ x: -1 }}
                                                                transition={{ duration: 0.2 }}
                                                            >
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                                            </motion.svg>
                                                        </motion.button>

                                                        {/* Next Button */}
                                                        <motion.button
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                handleManualNavigation("next");
                                                            }}
                                                            className="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700"
                                                            initial={{ opacity: 0, x: 10 }}
                                                            animate={{ 
                                                                opacity: isHovering ? 1 : 0,
                                                                x: isHovering ? 0 : 10
                                                            }}
                                                            whileHover={{ 
                                                                scale: 1.1,
                                                                backgroundColor: "rgba(255, 255, 255, 1)",
                                                                boxShadow: "0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)"
                                                            }}
                                                            transition={{ duration: 0.2, ease: "easeOut" }}
                                                            whileTap={{ scale: 0.95 }}
                                                        >
                                                            <motion.svg 
                                                                className="w-5 h-5" 
                                                                fill="none" 
                                                                stroke="currentColor" 
                                                                viewBox="0 0 24 24"
                                                                whileHover={{ x: 1 }}
                                                                transition={{ duration: 0.2 }}
                                                            >
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                                            </motion.svg>
                                                        </motion.button>

                                                        {/* Animated Image Counter */}
                                                        <motion.div 
                                                            className="absolute bottom-3 right-3 bg-black/60 backdrop-blur-sm text-white px-2 py-1 rounded-full text-xs font-medium"
                                                            key={currentImageIndex}
                                                            initial={{ scale: 0.8, opacity: 0.8 }}
                                                            animate={{ scale: 1, opacity: 1 }}
                                                            transition={{ duration: 0.2, ease: "easeOut" }}
                                                        >
                                                            {currentImageIndex + 1} / {allImages.length}
                                                        </motion.div>
                                                    </>
                                                )}
                                            </div>
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                                                <div className="text-center">
                                                    <div className="text-6xl mb-2">📦</div>
                                                    <div className="text-sm text-gray-500">No image available</div>
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {/* Animated Thumbnail Gallery */}
                                    {allImages.length > 1 && (
                                        <div className="grid grid-cols-4 gap-2">
                                            {allImages.slice(0, 4).map((image, index) => (
                                                <motion.div
                                                    key={index}
                                                    className={`aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 ${
                                                        currentImageIndex === index 
                                                            ? 'border-blue-500 shadow-md' 
                                                            : 'border-gray-200'
                                                    }`}
                                                    onClick={() => {
                                                        setCurrentImageIndex(index);
                                                        // Reset slideshow timer with fresh 5-second interval
                                                        if (!isLightboxOpen && !isHovering) {
                                                            startSlideshow();
                                                        }
                                                    }}
                                                    whileHover={{ 
                                                        scale: 1.05,
                                                        borderColor: currentImageIndex === index ? "#3b82f6" : "#93c5fd"
                                                    }}
                                                    whileTap={{ scale: 0.95 }}
                                                    animate={{
                                                        borderColor: currentImageIndex === index ? "#3b82f6" : "#e5e7eb",
                                                        boxShadow: currentImageIndex === index 
                                                            ? "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)"
                                                            : "0 0 0 0 rgba(0, 0, 0, 0)"
                                                    }}
                                                    transition={{ 
                                                        type: "spring", 
                                                        stiffness: 300, 
                                                        damping: 20 
                                                    }}
                                                >
                                                    <motion.img
                                                        src={image.image_url}
                                                        alt={image.caption}
                                                        className="w-full h-full object-cover"
                                                        animate={{
                                                            opacity: currentImageIndex === index ? 1 : 0.7
                                                        }}
                                                        whileHover={{ opacity: 1 }}
                                                        transition={{ duration: 0.2 }}
                                                    />
                                                    {/* Active indicator */}
                                                    {currentImageIndex === index && (
                                                        <motion.div 
                                                            className="absolute inset-0 bg-blue-500/10"
                                                            initial={{ opacity: 0 }}
                                                            animate={{ opacity: 1 }}
                                                            transition={{ duration: 0.2 }}
                                                        />
                                                    )}
                                                </motion.div>
                                            ))}
                                            {/* More images indicator */}
                                            {allImages.length > 4 && (
                                                <motion.div 
                                                    className="aspect-square bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 text-xs font-medium border-2 border-gray-200"
                                                    whileHover={{ scale: 1.05, borderColor: "#9ca3af" }}
                                                    transition={{ type: "spring", stiffness: 300, damping: 20 }}
                                                >
                                                    +{allImages.length - 4} more
                                                </motion.div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </motion.div>

                            {/* Product Details */}
                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                animate={
                                    contentInView ? { opacity: 1, x: 0 } : {}
                                }
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="space-y-8"
                            >
                                <div>
                                    <h1 className="text-4xl font-bold text-gray-900 mb-4">
                                        {product.name}
                                    </h1>
                                    <div className="text-3xl font-bold text-green-600 mb-6">
                                        {getDisplayPrice()}
                                        {product.price_unit && (
                                            <span className="text-lg text-gray-500 ml-2">
                                                / {product.price_unit}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {product.short_description && (
                                    <div className="text-lg text-gray-600 leading-relaxed">
                                        {product.short_description}
                                    </div>
                                )}

                                {/* Product Specifications */}
                                <div className="grid grid-cols-2 gap-4">
                                    {product.materials &&
                                        product.materials.length > 0 && (
                                            <div>
                                                <h4 className="font-semibold text-gray-900 mb-2">
                                                    Materials
                                                </h4>
                                                <div className="flex flex-wrap gap-2">
                                                    {product.materials.map(
                                                        (material, index) => (
                                                            <span
                                                                key={index}
                                                                className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
                                                            >
                                                                {material}
                                                            </span>
                                                        )
                                                    )}
                                                </div>
                                            </div>
                                        )}

                                    {product.colors &&
                                        product.colors.length > 0 && (
                                            <div>
                                                <h4 className="font-semibold text-gray-900 mb-2">
                                                    Colors
                                                </h4>
                                                <div className="flex flex-wrap gap-2">
                                                    {product.colors.map(
                                                        (color, index) => (
                                                            <span
                                                                key={index}
                                                                className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
                                                            >
                                                                {color}
                                                            </span>
                                                        )
                                                    )}
                                                </div>
                                            </div>
                                        )}

                                    {product.sizes &&
                                        product.sizes.length > 0 && (
                                            <div>
                                                <h4 className="font-semibold text-gray-900 mb-2">
                                                    Sizes
                                                </h4>
                                                <div className="flex flex-wrap gap-2">
                                                    {product.sizes.map(
                                                        (size, index) => (
                                                            <span
                                                                key={index}
                                                                className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
                                                            >
                                                                {size}
                                                            </span>
                                                        )
                                                    )}
                                                </div>
                                            </div>
                                        )}

                                    {product.minimum_order && (
                                        <div>
                                            <h4 className="font-semibold text-gray-900 mb-2">
                                                Minimum Order
                                            </h4>
                                            <p className="text-gray-600">
                                                {product.minimum_order} pieces
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {/* E-commerce Links */}
                                {product.ecommerce_links &&
                                    product.ecommerce_links.length > 0 && (
                                        <div>
                                            <h4 className="font-semibold text-gray-900 mb-4">
                                                Buy Now
                                            </h4>
                                            <div className="grid grid-cols-1 gap-3">
                                                {product.ecommerce_links.map(
                                                    (link) => (
                                                        <button
                                                            key={link.id}
                                                            onClick={() =>
                                                                handleLinkClick(
                                                                    link
                                                                )
                                                            }
                                                            className="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-lg transition-all duration-300 bg-white hover:bg-blue-50 group"
                                                        >
                                                            <div className="flex items-center">
                                                                <div className="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                                                    {link.platform ===
                                                                        "tokopedia" &&
                                                                        "🟢"}
                                                                    {link.platform ===
                                                                        "shopee" &&
                                                                        "🟠"}
                                                                    {link.platform ===
                                                                        "instagram" &&
                                                                        "📷"}
                                                                    {link.platform ===
                                                                        "whatsapp" &&
                                                                        "💬"}
                                                                    {![
                                                                        "tokopedia",
                                                                        "shopee",
                                                                        "instagram",
                                                                        "whatsapp",
                                                                    ].includes(
                                                                        link.platform
                                                                    ) && "🛒"}
                                                                </div>
                                                                <div className="text-left">
                                                                    <div className="font-medium text-gray-900">
                                                                        {link.platform_display_name ||
                                                                            formatPlatformName(link.platform)}
                                                                    </div>
                                                                    {link.store_name && (
                                                                        <div className="text-sm text-gray-500">
                                                                            {
                                                                                link.store_name
                                                                            }
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                            <div className="text-right">
                                                                {link.price_on_platform && (
                                                                    <div className="font-medium text-gray-900">
                                                                        Rp{" "}
                                                                        {new Intl.NumberFormat(
                                                                            "id-ID"
                                                                        ).format(
                                                                            link.price_on_platform
                                                                        )}
                                                                    </div>
                                                                )}
                                                                <svg
                                                                    className="w-5 h-5 text-gray-400 ml-auto"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    viewBox="0 0 24 24"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                                    />
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    )
                                                )}
                                            </div>
                                        </div>
                                    )}

                                {/* Tags */}
                                {product.tags && product.tags.length > 0 && (
                                    <div>
                                        <h4 className="font-semibold text-gray-900 mb-3">
                                            Tags
                                        </h4>
                                        <div className="flex flex-wrap gap-2">
                                            {product.tags.map((tag) => (
                                                <span
                                                    key={tag.id}
                                                    className="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium"
                                                >
                                                    #{tag.name}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </motion.div>
                        </div>

                        {/* Product Description */}
                        {product.description && (
                            <motion.div
                                initial={{ opacity: 0, y: 30 }}
                                animate={
                                    contentInView ? { opacity: 1, y: 0 } : {}
                                }
                                transition={{ duration: 0.8, delay: 0.6 }}
                                className="mt-16 prose prose-lg max-w-none"
                            >
                                <h3 className="text-2xl font-bold text-gray-900 mb-6">
                                    Description
                                </h3>
                                <div
                                    className="text-gray-600 leading-relaxed"
                                    dangerouslySetInnerHTML={{
                                        __html: product.description,
                                    }}
                                />
                            </motion.div>
                        )}
                    </motion.div>
                </div>
            </section>

            {/* Related Products */}
            {relatedProducts && relatedProducts.length > 0 && (
                <section className="py-20 bg-gray-900 text-white relative">
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-3xl font-bold text-center mb-12 text-white"
                        >
                            Related Products from {village?.name}
                        </motion.h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            {relatedProducts.map((relatedProduct, index) => (
                                <div
                                    key={relatedProduct.id}
                                    className="[&_*]:text-white [&_*]:border-white/30"
                                >
                                    <ProductCard
                                        product={relatedProduct}
                                        index={index}
                                        village={village}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Related Stories */}
            {relatedStories && relatedStories.length > 0 && (
                <section className="py-20 bg-gray-900 text-white relative">
                    <div className="absolute inset-0 backdrop-blur-sm" />
                    <div className="container mx-auto px-6 relative z-10">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-3xl font-bold text-center mb-12 text-white"
                        >
                            Stories about {product.name}
                        </motion.h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {relatedStories.map((story, index) => (
                                <div
                                    key={story.id}
                                    className="[&_*]:text-white [&_*]:border-white/30"
                                >
                                    <ArticleCard
                                        article={story}
                                        index={index}
                                        village={village}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Lightbox Modal */}
            {isLightboxOpen && (
                <ProductLightboxModal
                    images={allImages}
                    currentIndex={currentImageIndex}
                    onClose={closeLightbox}
                    onNavigate={navigateImage}
                />
            )}
        </MainLayout>
    );
};

// Product Lightbox Modal Component
const ProductLightboxModal = ({ images, currentIndex, onClose, onNavigate }) => {
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

    const currentImage = images[currentIndex];

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
                {images.length > 1 && (
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
                    <AnimatePresence mode="wait">
                        <motion.img
                            key={currentIndex}
                            src={currentImage.image_url}
                            alt={currentImage.caption}
                            className="max-w-full max-h-[80vh] object-contain mx-auto rounded-lg shadow-2xl"
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9 }}
                            transition={{ duration: 0.3 }}
                        />
                    </AnimatePresence>

                    {/* Image Info */}
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 rounded-b-lg">
                        <div className="flex items-center justify-between text-white">
                            <div>
                                <div className="text-lg font-semibold mb-1">
                                    {currentImage.caption}
                                </div>
                                {currentImage.is_primary && (
                                    <div className="text-sm text-blue-300 mb-2">
                                        ⭐ Primary Image
                                    </div>
                                )}
                            </div>
                            <div className="text-sm text-gray-400">
                                {currentIndex + 1} / {images.length}
                            </div>
                        </div>
                    </div>
                </motion.div>
            </div>
        </motion.div>
    );
};

export default ProductShowPage;
