import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const ProductsPage = ({ village, products, categories, filters }) => {
    const [filteredProducts, setFilteredProducts] = useState(products.data);
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filters.category || ""
    );
    const [sortBy, setSortBy] = useState(filters.sort || "featured");
    const { scrollY } = useScroll();

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 500], [0, -150]);
    const overlayOpacity = useTransform(scrollY, [0, 300], [0.3, 0.7]);

    useEffect(() => {
        let filtered = products.data;

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(
                (product) =>
                    product.name
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    product.description
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase())
            );
        }

        // Filter by category
        if (selectedCategory) {
            filtered = filtered.filter(
                (product) => product.category?.id === selectedCategory
            );
        }

        // Sort products
        switch (sortBy) {
            case "name":
                filtered.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case "price_low":
                filtered.sort(
                    (a, b) =>
                        (a.price || a.price_range_min || 0) -
                        (b.price || b.price_range_min || 0)
                );
                break;
            case "price_high":
                filtered.sort(
                    (a, b) =>
                        (b.price || b.price_range_max || 0) -
                        (a.price || a.price_range_max || 0)
                );
                break;
            case "popular":
                filtered.sort((a, b) => b.view_count - a.view_count);
                break;
            default:
                // Featured first, then newest
                filtered.sort((a, b) => {
                    if (a.is_featured && !b.is_featured) return -1;
                    if (!a.is_featured && b.is_featured) return 1;
                    return new Date(b.created_at) - new Date(a.created_at);
                });
        }

        setFilteredProducts(filtered);
    }, [searchTerm, selectedCategory, sortBy, products.data]);

    return (
        <MainLayout title="Products">
            <Head title={`Products - ${village?.name}`} />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden">
                {/* Background with parallax */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 bg-gradient-to-b from-emerald-600 via-green-500 to-green-700"
                >
                    <div className="absolute inset-0 opacity-20">
                        <svg viewBox="0 0 1200 600" className="w-full h-full">
                            <path
                                d="M0,600 L0,200 Q300,150 600,180 T1200,160 L1200,600 Z"
                                fill="#1a472a"
                            />
                            <path
                                d="M0,600 L0,300 Q400,250 800,270 T1200,250 L1200,600 Z"
                                fill="#22543d"
                            />
                            <path
                                d="M0,600 L0,400 Q500,350 1000,370 T1200,350 L1200,600 Z"
                                fill="#2f855a"
                            />
                        </svg>
                    </div>
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
                            Local Products
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            Discover authentic products from {village?.name}
                        </motion.p>

                        {/* Filter Controls */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.5 }}
                            className="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            {/* Search */}
                            <div className="relative">
                                <input
                                    type="text"
                                    placeholder="Search products..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50"
                                />
                            </div>

                            {/* Category Filter */}
                            <select
                                value={selectedCategory}
                                onChange={(e) =>
                                    setSelectedCategory(e.target.value)
                                }
                                className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                            >
                                <option value="">All Categories</option>
                                {categories?.map((category) => (
                                    <option
                                        key={category.id}
                                        value={category.id}
                                        className="text-black"
                                    >
                                        {category.name}
                                    </option>
                                ))}
                            </select>

                            {/* Sort */}
                            <select
                                value={sortBy}
                                onChange={(e) => setSortBy(e.target.value)}
                                className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                            >
                                <option value="featured" className="text-black">
                                    Featured
                                </option>
                                <option value="name" className="text-black">
                                    Name
                                </option>
                                <option
                                    value="price_low"
                                    className="text-black"
                                >
                                    Price: Low to High
                                </option>
                                <option
                                    value="price_high"
                                    className="text-black"
                                >
                                    Price: High to Low
                                </option>
                                <option value="popular" className="text-black">
                                    Most Popular
                                </option>
                            </select>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Products Grid Section */}
            <section className="py-20 bg-gradient-to-b from-green-700 to-emerald-900">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="mb-12"
                    >
                        <h2 className="text-4xl font-bold text-white text-center mb-4">
                            {filteredProducts.length} Product
                            {filteredProducts.length !== 1 ? "s" : ""} Found
                        </h2>
                        <div className="w-24 h-1 bg-gradient-to-r from-yellow-400 to-orange-500 mx-auto"></div>
                    </motion.div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        {filteredProducts.map((product, index) => (
                            <ProductCard
                                key={product.id}
                                product={product}
                                index={index}
                                village={village}
                            />
                        ))}
                    </div>

                    {filteredProducts.length === 0 && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            className="text-center py-20"
                        >
                            <div className="text-6xl mb-4">🛍️</div>
                            <h3 className="text-2xl font-semibold text-white mb-2">
                                No Products Found
                            </h3>
                            <p className="text-gray-400">
                                Try adjusting your search or filters
                            </p>
                        </motion.div>
                    )}

                    {/* Pagination */}
                    {products.last_page > 1 && (
                        <Pagination products={products} />
                    )}
                </div>
            </section>

            {/* Featured Categories */}
            <section className="py-20 bg-gradient-to-b from-emerald-900 to-green-800">
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-4xl font-bold text-white text-center mb-12"
                    >
                        Shop by Category
                    </motion.h2>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                        {categories?.slice(0, 8).map((category, index) => (
                            <motion.button
                                key={category.id}
                                initial={{ opacity: 0, y: 30 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{
                                    duration: 0.6,
                                    delay: index * 0.1,
                                }}
                                whileHover={{ scale: 1.05, y: -5 }}
                                whileTap={{ scale: 0.95 }}
                                onClick={() => setSelectedCategory(category.id)}
                                className={`p-6 rounded-xl backdrop-blur-md border transition-all duration-300 ${
                                    selectedCategory === category.id
                                        ? "bg-white/20 border-white/40"
                                        : "bg-white/10 border-white/20 hover:bg-white/15"
                                }`}
                            >
                                <div className="text-3xl mb-3">
                                    {category.type === "sme" ? "🏪" : "🎯"}
                                </div>
                                <h3 className="text-white font-semibold text-lg">
                                    {category.name}
                                </h3>
                                <p className="text-gray-300 text-sm mt-1">
                                    {category.products_count || 0} products
                                </p>
                            </motion.button>
                        ))}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
};

const ProductCard = ({ product, index, village }) => {
    const [ref, inView] = useInView({
        threshold: 0.1,
        triggerOnce: true,
    });

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

    const getAvailabilityColor = () => {
        switch (product.availability) {
            case "available":
                return "bg-green-500";
            case "out_of_stock":
                return "bg-red-500";
            case "seasonal":
                return "bg-yellow-500";
            case "on_demand":
                return "bg-blue-500";
            default:
                return "bg-gray-500";
        }
    };

    return (
        <motion.div
            ref={ref}
            initial={{ opacity: 0, y: 50 }}
            animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ duration: 0.6, delay: index * 0.1 }}
            whileHover={{ y: -10, scale: 1.02 }}
            className="group bg-white/5 backdrop-blur-md rounded-2xl overflow-hidden border border-white/10 hover:border-white/30 transition-all duration-300"
        >
            <Link href={`/products/${product.slug}`}>
                <div className="relative h-56 overflow-hidden">
                    {product.primary_image_url ? (
                        <img
                            src={product.primary_image_url}
                            alt={product.name}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                            <span className="text-4xl text-white">📦</span>
                        </div>
                    )}

                    {/* Overlay with info */}
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-300" />

                    {/* Availability badge */}
                    <div
                        className={`absolute top-4 left-4 px-2 py-1 rounded-full text-xs font-medium text-white ${getAvailabilityColor()}`}
                    >
                        {product.availability?.replace("_", " ").toUpperCase()}
                    </div>

                    {/* Featured badge */}
                    {product.is_featured && (
                        <div className="absolute top-4 right-4 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            ⭐ Featured
                        </div>
                    )}

                    {/* View count */}
                    <div className="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white px-2 py-1 rounded-full text-xs">
                        👁️ {product.view_count}
                    </div>
                </div>

                <div className="p-6">
                    <div className="flex items-center justify-between mb-2">
                        {product.category && (
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                                {product.category.name}
                            </span>
                        )}
                        {product.place && (
                            <span className="text-xs text-gray-400">
                                📍 {product.place.name}
                            </span>
                        )}
                    </div>

                    <h3 className="text-xl font-bold text-white mb-2 group-hover:text-green-300 transition-colors duration-300 line-clamp-2">
                        {product.name}
                    </h3>

                    <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed text-sm">
                        {product.short_description ||
                            product.description
                                ?.replace(/<[^>]*>/g, "")
                                .substring(0, 120)}
                        {product.description?.length > 120 && "..."}
                    </p>

                    <div className="flex items-center justify-between">
                        <div className="text-green-300 font-bold text-lg">
                            {getDisplayPrice()}
                            {product.price_unit && (
                                <span className="text-xs text-gray-400 ml-1">
                                    /{product.price_unit}
                                </span>
                            )}
                        </div>
                        <span className="group-hover:text-green-300 transition-colors duration-300 text-sm">
                            View Details →
                        </span>
                    </div>

                    {/* E-commerce links count */}
                    {product.ecommerce_links_count > 0 && (
                        <div className="mt-3 text-xs text-gray-400">
                            🛒 Available on {product.ecommerce_links_count}{" "}
                            platform
                            {product.ecommerce_links_count !== 1 ? "s" : ""}
                        </div>
                    )}

                    {/* Tags */}
                    {product.tags && product.tags.length > 0 && (
                        <div className="mt-3 flex flex-wrap gap-1">
                            {product.tags.slice(0, 3).map((tag) => (
                                <span
                                    key={tag.id}
                                    className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded"
                                >
                                    #{tag.name}
                                </span>
                            ))}
                            {product.tags.length > 3 && (
                                <span className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded">
                                    +{product.tags.length - 3}
                                </span>
                            )}
                        </div>
                    )}
                </div>
            </Link>
        </motion.div>
    );
};

const Pagination = ({ products }) => {
    const { current_page, last_page } = products;

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
                                    ? "bg-green-500 text-white"
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

export default ProductsPage;
