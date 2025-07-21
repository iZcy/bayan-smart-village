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

const ProductsPage = ({ village, products, categories = [], filters = {} }) => {
    // Ensure we have valid data
    const productData = products?.data || [];
    const filterData = filters || {};
    const categoryData = categories || [];

    const [filteredProducts, setFilteredProducts] = useState(productData);
    const [searchTerm, setSearchTerm] = useState(filterData.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filterData.category || ""
    );
    const [sortBy, setSortBy] = useState(filterData.sort || "featured");

    useEffect(() => {
        let filtered = productData;

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
    }, [searchTerm, selectedCategory, sortBy, productData]);

    // Sort options for products
    const sortOptions = [
        { value: "featured", label: "Featured" },
        { value: "newest", label: "Newest" },
        { value: "oldest", label: "Oldest" },
        { value: "name", label: "Name A-Z" },
        { value: "price_low", label: "Price: Low to High" },
        { value: "price_high", label: "Price: High to Low" },
        { value: "popular", label: "Most Popular" },
    ];

    const handleClearFilters = () => {
        setSearchTerm("");
        setSelectedCategory("");
        setSortBy("featured");
    };

    // Sort filter component
    const sortFilterComponent = (
        <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value)}
            className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
        >
            {sortOptions.map((option) => (
                <option
                    key={option.value}
                    value={option.value}
                    className="text-black"
                >
                    {option.label}
                </option>
            ))}
        </select>
    );

    return (
        <MainLayout title="Products">
            <Head title={`Products - ${village?.name}`} />

            {/* Hero Section with Integrated Filters */}
            <HeroSection
                title="Local Products"
                subtitle={`Discover authentic products from ${village?.name}`}
                backgroundGradient="from-emerald-600 via-green-500 to-green-700"
                parallax={true}
                scrollY={{ useTransform: (scrollY) => scrollY }}
            >
                <FilterControls
                    searchTerm={searchTerm}
                    setSearchTerm={setSearchTerm}
                    selectedCategory={selectedCategory}
                    setSelectedCategory={setSelectedCategory}
                    categories={categoryData}
                    additionalFilters={[{ component: sortFilterComponent }]}
                    searchPlaceholder="Search products..."
                    className="max-w-4xl mx-auto"
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
                    {products?.last_page > 1 && (
                        <Pagination
                            paginationData={products}
                            theme="products"
                        />
                    )}
                </div>
            </section>
        </MainLayout>
    );
};

export default ProductsPage;
