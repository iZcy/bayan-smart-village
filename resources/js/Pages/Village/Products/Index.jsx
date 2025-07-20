// resources/js/Pages/Products/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { ProductCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";

const ProductsPage = ({ village, products, categories, filters }) => {
    const [filteredProducts, setFilteredProducts] = useState(products.data);
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filters.category || ""
    );
    const [sortBy, setSortBy] = useState(filters.sort || "featured");

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
                        ?.toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    product.short_description
                        ?.toLowerCase()
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
                filtered.sort(
                    (a, b) => (b.view_count || 0) - (a.view_count || 0)
                );
                break;
            case "newest":
                filtered.sort(
                    (a, b) => new Date(b.created_at) - new Date(a.created_at)
                );
                break;
            case "oldest":
                filtered.sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );
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
            <HeroSection
                title="Local Products"
                subtitle={`Discover authentic products from ${village?.name}`}
                backgroundGradient="from-emerald-600 via-green-500 to-green-700"
                enableParallax={true}
            >
                <FilterControls
                    searchTerm={searchTerm}
                    setSearchTerm={setSearchTerm}
                    selectedCategory={selectedCategory}
                    setSelectedCategory={setSelectedCategory}
                    categories={categories}
                    sortBy={sortBy}
                    setSortBy={setSortBy}
                    searchPlaceholder="Search products..."
                />
            </HeroSection>

            {/* Products Grid Section */}
            <section className="py-20 bg-gradient-to-b from-green-700 to-emerald-900">
                <div className="container mx-auto px-6">
                    <SectionHeader
                        title="Product"
                        count={filteredProducts.length}
                        gradientColor="from-yellow-400 to-orange-500"
                    />

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
                        <Pagination
                            paginationData={products}
                            theme="products"
                        />
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
                                    {category.icon || "🎯"}
                                </div>
                                <h3 className="text-white font-semibold text-lg">
                                    {category.name}
                                </h3>
                                <p className="text-gray-300 text-sm mt-1">
                                    {category.offers_count || 0} products
                                </p>
                            </motion.button>
                        ))}
                    </div>
                </div>
            </section>

            {/* Product Types Section */}
            <section className="py-20 bg-gradient-to-b from-green-800 to-emerald-900">
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-4xl font-bold text-white text-center mb-12"
                    >
                        Product Highlights
                    </motion.h2>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {[
                            {
                                title: "Featured Products",
                                description:
                                    "Handpicked items from our village artisans",
                                icon: "⭐",
                                count:
                                    products.data?.filter((p) => p.is_featured)
                                        .length || 0,
                                color: "from-yellow-400 to-orange-500",
                            },
                            {
                                title: "Available Now",
                                description:
                                    "Products ready for immediate purchase",
                                icon: "✅",
                                count:
                                    products.data?.filter(
                                        (p) => p.availability === "available"
                                    ).length || 0,
                                color: "from-green-400 to-emerald-500",
                            },
                            {
                                title: "Seasonal Items",
                                description:
                                    "Limited time offerings based on harvest seasons",
                                icon: "🌱",
                                count:
                                    products.data?.filter(
                                        (p) => p.availability === "seasonal"
                                    ).length || 0,
                                color: "from-blue-400 to-cyan-500",
                            },
                        ].map((type, index) => (
                            <motion.div
                                key={type.title}
                                initial={{ opacity: 0, y: 50 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                transition={{
                                    duration: 0.6,
                                    delay: index * 0.2,
                                }}
                                whileHover={{ scale: 1.05, y: -10 }}
                                className="bg-white/10 backdrop-blur-md rounded-xl p-8 border border-white/20 text-center"
                            >
                                <motion.div
                                    className={`w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r ${type.color} flex items-center justify-center text-2xl`}
                                    animate={{ rotate: [0, 5, -5, 0] }}
                                    transition={{
                                        duration: 2,
                                        repeat: Infinity,
                                        delay: index,
                                    }}
                                >
                                    {type.icon}
                                </motion.div>
                                <h3 className="text-xl font-bold text-white mb-2">
                                    {type.title}
                                </h3>
                                <p className="text-gray-300 text-sm mb-4">
                                    {type.description}
                                </p>
                                <div className="text-3xl font-bold text-white">
                                    {type.count}
                                </div>
                                <div className="text-gray-400 text-sm">
                                    products
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Stats Section */}
            <section className="py-20 bg-gradient-to-b from-emerald-900 to-green-800">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="grid grid-cols-2 md:grid-cols-4 gap-8"
                    >
                        {[
                            {
                                label: "Total Products",
                                value: products.total || 0,
                                icon: "📦",
                            },
                            {
                                label: "Categories",
                                value: categories?.length || 0,
                                icon: "🏷️",
                            },
                            {
                                label: "Local Artisans",
                                value:
                                    new Set(
                                        products.data
                                            ?.map((p) => p.sme?.id)
                                            .filter(Boolean)
                                    ).size || 0,
                                icon: "👨‍🎨",
                            },
                            {
                                label: "Villages Represented",
                                value: 1,
                                icon: "🏘️",
                            },
                        ].map((stat, index) => (
                            <motion.div
                                key={stat.label}
                                initial={{ scale: 0, rotateY: 90 }}
                                whileInView={{ scale: 1, rotateY: 0 }}
                                transition={{
                                    delay: index * 0.2,
                                    duration: 0.6,
                                    type: "spring",
                                }}
                                whileHover={{ scale: 1.1, y: -5 }}
                                className="text-center text-white bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/20"
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
                                <div className="text-4xl font-bold mb-2">
                                    {stat.value}
                                </div>
                                <div className="text-gray-300">
                                    {stat.label}
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>
                </div>
            </section>
        </MainLayout>
    );
};

export default ProductsPage;
