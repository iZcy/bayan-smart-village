import React, { useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform, useSpring } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const ProductShowPage = ({ village, product, relatedProducts }) => {
    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Parallax effects for hero section
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroScale = useTransform(scrollY, [0, 800], [1, 1.1]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

    // Geometric elements animation
    const geometryY = useTransform(scrollY, [0, 1000], [0, -300]);
    const geometryRotate = useTransform(scrollY, [0, 1000], [0, 45]);

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    // Village music
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

    const handleLinkClick = async (linkId) => {
        try {
            const response = await fetch(
                `/products/${product.id}/links/${linkId}/click`,
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
            }
        } catch (error) {
            console.error("Error tracking link click:", error);
        }
    };

    return (
        <MainLayout title={product.name}>
            <Head title={`${product.name} - ${village?.name}`} />

            {/* Background Audio */}
            <audio ref={audioRef} loop>
                <source src="/audio/village-ambient.mp3" type="audio/mpeg" />
            </audio>

            {/* Hero Section - Firewatch Style */}
            <section className="relative h-screen overflow-hidden">
                {/* Replace the existing hero background with this enhanced version */}
                <motion.div
                    style={{ y: heroY, scale: heroScale, opacity: heroOpacity }}
                    className="absolute inset-0"
                >
                    {product.primary_image_url ? (
                        <div className="relative w-full h-full">
                            <img
                                src={product.primary_image_url}
                                alt={product.name}
                                className="w-full h-full object-cover"
                            />
                            {/* Product-specific overlay */}
                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/30" />
                        </div>
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-green-400 via-blue-500 to-purple-600 relative">
                            {/* Product-themed geometric background */}
                            <svg
                                viewBox="0 0 1200 600"
                                className="absolute inset-0 w-full h-full"
                            >
                                {/* Shopping/commerce themed shapes */}
                                <motion.rect
                                    x="100"
                                    y="200"
                                    width="150"
                                    height="200"
                                    fill="rgba(255,255,255,0.1)"
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    transition={{ delay: 1, duration: 2 }}
                                />
                                <motion.circle
                                    cx="800"
                                    cy="150"
                                    r="80"
                                    fill="rgba(255,255,255,0.08)"
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    transition={{ delay: 1.5, duration: 2 }}
                                />

                                {/* Mountain silhouettes for products */}
                                <motion.path
                                    initial={{ pathLength: 0 }}
                                    animate={{ pathLength: 1 }}
                                    transition={{ duration: 3, delay: 0.5 }}
                                    d="M0,600 L0,250 Q300,200 600,230 T1200,210 L1200,600 Z"
                                    fill="rgba(26, 26, 26, 0.7)"
                                />
                            </svg>

                            {/* Floating product icons */}
                            {[...Array(6)].map((_, i) => (
                                <motion.div
                                    key={i}
                                    className="absolute text-white/20 text-2xl"
                                    style={{
                                        left: `${20 + Math.random() * 60}%`,
                                        top: `${20 + Math.random() * 40}%`,
                                    }}
                                    animate={{
                                        y: [0, -30, 0],
                                        rotate: [0, 180, 360],
                                        opacity: [0.2, 0.4, 0.2],
                                    }}
                                    transition={{
                                        duration: 4 + Math.random() * 2,
                                        repeat: Infinity,
                                        delay: Math.random() * 2,
                                    }}
                                >
                                    {["🛍️", "📦", "🏷️", "💎", "🎁", "⭐"][i]}
                                </motion.div>
                            ))}
                        </div>
                    )}
                </motion.div>

                {/* Enhanced product badges in hero content */}
                <motion.div
                    initial={{ opacity: 0, y: 100 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 1.2, delay: 0.5 }}
                    className="mb-6 flex flex-wrap justify-center gap-3"
                >
                    {product.place && (
                        <motion.span
                            className="inline-block px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/20"
                            whileHover={{
                                scale: 1.05,
                                backgroundColor: "rgba(255,255,255,0.3)",
                            }}
                        >
                            🏪 {product.place.name}
                        </motion.span>
                    )}
                    {product.category && (
                        <motion.span
                            className="inline-block px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium border border-white/20"
                            whileHover={{
                                scale: 1.05,
                                backgroundColor: "rgba(255,255,255,0.3)",
                            }}
                        >
                            📦 {product.category.name}
                        </motion.span>
                    )}
                    {product.is_featured && (
                        <motion.span
                            className="inline-block px-4 py-2 bg-gradient-to-r from-yellow-400/30 to-orange-400/30 backdrop-blur-md text-white rounded-full text-sm font-medium border border-yellow-400/30"
                            whileHover={{ scale: 1.05 }}
                            animate={{
                                boxShadow: [
                                    "0 0 0 rgba(255,255,0,0)",
                                    "0 0 20px rgba(255,255,0,0.3)",
                                    "0 0 0 rgba(255,255,0,0)",
                                ],
                            }}
                            transition={{ duration: 2, repeat: Infinity }}
                        >
                            ⭐ Featured
                        </motion.span>
                    )}
                </motion.div>

                {/* Geometric Elements - Enroute Health Style */}
                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute top-20 right-20 w-32 h-32 opacity-20"
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
                    </svg>
                </motion.div>

                <motion.div
                    style={{ y: geometryY, rotate: geometryRotate }}
                    className="absolute bottom-32 left-16 w-24 h-24 opacity-15"
                >
                    <svg
                        viewBox="0 0 100 100"
                        className="w-full h-full text-white"
                    >
                        <rect
                            x="20"
                            y="20"
                            width="60"
                            height="60"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="3"
                        />
                        <rect
                            x="35"
                            y="35"
                            width="30"
                            height="30"
                            fill="currentColor"
                            opacity="0.4"
                        />
                    </svg>
                </motion.div>

                {/* Overlay gradient */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/30" />

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                    <div className="max-w-4xl px-6">
                        <motion.div
                            initial={{ opacity: 0, y: 100 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1.2, delay: 0.5 }}
                            className="mb-6"
                        >
                            {product.place && (
                                <span className="inline-block px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium mb-4">
                                    🏪 {product.place.name}
                                </span>
                            )}
                            {product.category && (
                                <span className="inline-block px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-full text-sm font-medium mb-4 ml-2">
                                    📦 {product.category.name}
                                </span>
                            )}
                        </motion.div>

                        <motion.h1
                            initial={{ opacity: 0, y: 80 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.8 }}
                            className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                        >
                            {product.name}
                        </motion.h1>

                        <motion.div
                            initial={{ opacity: 0, y: 60 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1.1 }}
                            className="flex items-center justify-center space-x-6 text-white/80"
                        >
                            <span className="text-2xl font-bold text-green-300">
                                {getDisplayPrice()}
                            </span>
                            <span
                                className={`px-3 py-1 rounded-full text-sm font-medium ${
                                    product.availability === "available"
                                        ? "bg-green-500"
                                        : product.availability ===
                                          "out_of_stock"
                                        ? "bg-red-500"
                                        : product.availability === "seasonal"
                                        ? "bg-yellow-500"
                                        : "bg-blue-500"
                                }`}
                            >
                                {product.availability?.replace("_", " ")}
                            </span>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.4 }}
                            className="mt-8"
                        >
                            <button
                                onClick={() => {
                                    document
                                        .getElementById("content")
                                        .scrollIntoView({
                                            behavior: "smooth",
                                        });
                                }}
                                className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300"
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
                            </button>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Product Content */}
            <section id="content" className="relative py-20 bg-white">
                {/* Geometric decorations */}
                <div className="absolute top-0 left-0 w-64 h-64 opacity-5">
                    <svg
                        viewBox="0 0 200 200"
                        className="w-full h-full text-gray-900"
                    >
                        <defs>
                            <pattern
                                id="grid"
                                width="20"
                                height="20"
                                patternUnits="userSpaceOnUse"
                            >
                                <path
                                    d="M 20 0 L 0 0 0 20"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1"
                                />
                            </pattern>
                        </defs>
                        <rect width="200" height="200" fill="url(#grid)" />
                    </svg>
                </div>

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
                                animate={
                                    contentInView ? { opacity: 1, x: 0 } : {}
                                }
                                transition={{ duration: 0.8, delay: 0.2 }}
                            >
                                <div className="sticky top-20">
                                    <div className="aspect-square bg-gray-100 rounded-2xl overflow-hidden mb-4">
                                        {product.primary_image_url ? (
                                            <img
                                                src={product.primary_image_url}
                                                alt={product.name}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-gray-400">
                                                <span className="text-6xl">
                                                    📦
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Additional images if available */}
                                    {product.images &&
                                        product.images.length > 0 && (
                                            <div className="grid grid-cols-4 gap-2">
                                                {product.images
                                                    .slice(0, 4)
                                                    .map((image, index) => (
                                                        <div
                                                            key={index}
                                                            className="aspect-square bg-gray-100 rounded-lg overflow-hidden"
                                                        >
                                                            <img
                                                                src={
                                                                    image.image_url
                                                                }
                                                                alt={`${
                                                                    product.name
                                                                } ${index + 1}`}
                                                                className="w-full h-full object-cover"
                                                            />
                                                        </div>
                                                    ))}
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
                                                                    link.id
                                                                )
                                                            }
                                                            className="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:shadow-md transition-all duration-300"
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
                                                                        {link.platform_name ||
                                                                            link.platform}
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
                                                                    className="w-5 h-5 text-gray-400"
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
                <section className="py-20 bg-gray-50">
                    <div className="container mx-auto px-6">
                        <motion.h2
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-3xl font-bold text-center mb-12"
                        >
                            Related Products
                        </motion.h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            {relatedProducts.map((relatedProduct, index) => (
                                <motion.div
                                    key={relatedProduct.id}
                                    initial={{ opacity: 0, y: 50 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    transition={{
                                        duration: 0.6,
                                        delay: index * 0.1,
                                    }}
                                    whileHover={{ y: -10, scale: 1.02 }}
                                    className="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    <Link
                                        href={`/products/${relatedProduct.slug}`}
                                    >
                                        <div className="aspect-square bg-gray-100 relative overflow-hidden">
                                            {relatedProduct.primary_image_url ? (
                                                <img
                                                    src={
                                                        relatedProduct.primary_image_url
                                                    }
                                                    alt={relatedProduct.name}
                                                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center">
                                                    <span className="text-4xl text-gray-400">
                                                        📦
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="p-6">
                                            <h3 className="text-lg font-bold mb-2 group-hover:text-blue-600 transition-colors duration-300 line-clamp-2">
                                                {relatedProduct.name}
                                            </h3>
                                            <p className="text-green-600 font-semibold">
                                                {relatedProduct.display_price}
                                            </p>
                                        </div>
                                    </Link>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </MainLayout>
    );
};

export default ProductShowPage;
