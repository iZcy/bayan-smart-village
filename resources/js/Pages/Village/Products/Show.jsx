// resources/js/Pages/Village/Products/Show.jsx
import React, { useEffect, useRef } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import MediaBackground from "@/Components/MediaBackground";
import HeroSection from "@/Components/HeroSection";
import { ProductCard } from "@/Components/Cards/Index";

const ProductShowPage = ({ village, product, relatedProducts }) => {
    const { scrollY } = useScroll();
    const audioRef = useRef(null);

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 800], [0, -200]);
    const heroOpacity = useTransform(scrollY, [0, 400], [1, 0.8]);

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

            {/* Media Background */}
            <MediaBackground
                context="product"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
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
                                ?.replace("_", " ")
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

                    {/* View Details Button */}
                    <motion.button
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.8, delay: 2 }}
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

                                    {/* Additional images */}
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
        </MainLayout>
    );
};

export default ProductShowPage;
