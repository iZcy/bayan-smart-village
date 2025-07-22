// resources/js/Pages/Products/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import {
    motion,
    AnimatePresence,
    useScroll,
    useTransform,
} from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import MediaBackground from "@/Components/MediaBackground";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { ProductCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";
import SlideshowBackground from "@/Components/SlideshowBackground";
import { useSlideshowData, slideshowConfigs } from "@/hooks/useSlideshowData";

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
    const { scrollY } = useScroll();

    // Prepare slideshow data using the custom hook
    const slideshowImages = useSlideshowData(productData, slideshowConfigs.products);

    // Color overlay for Products sections - multiple scroll points for footer visibility
    const colorOverlay = useTransform(
        scrollY,
        [0, 800, 1600, 2400],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.5))", // Hero - darker for better card visibility
            "linear-gradient(to bottom, rgba(34,197,94,0.7), rgba(0,135,90,0.8))", // Products Grid - green, darker for better card visibility
            "linear-gradient(to bottom, rgba(0,135,90,0.6), rgba(6,78,59,0.7))", // Mid transition
            "linear-gradient(to bottom, rgba(6,78,59,0.4), rgba(0,0,0,0.6))", // End fade to black for footer
        ]
    );

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

            {/* Media Background with blur for content sections */}
            <MediaBackground
                context="products"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                controlsId="products-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Enhanced Color Overlay */}
            <motion.div
                className="fixed inset-0 z-5 pointer-events-none"
                style={{ background: colorOverlay }}
            />

            {/* Slideshow Background */}
            <SlideshowBackground
                images={slideshowImages}
                interval={slideshowConfigs.products.interval}
                transitionDuration={slideshowConfigs.products.transitionDuration}
                placeholderConfig={slideshowConfigs.products.placeholderConfig}
            />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden z-10">
                {/* Content overlay for readability */}
                <div className="absolute inset-0 bg-black/40 z-5"></div>

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-20 flex-col gap-4">
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

                        <FilterControls
                            searchTerm={searchTerm}
                            setSearchTerm={setSearchTerm}
                            selectedCategory={selectedCategory}
                            setSelectedCategory={setSelectedCategory}
                            categories={categoryData}
                            additionalFilters={[
                                { component: sortFilterComponent },
                            ]}
                            searchPlaceholder="Search products..."
                            className="max-w-4xl mx-auto relative z-25"
                        />
                    </div>

                    {/* Scroll Indicator */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 2, duration: 1 }}
                        className="transform text-white z-30"
                    >
                        <motion.div
                            animate={{ y: [0, 10, 0] }}
                            transition={{ repeat: Infinity, duration: 2 }}
                            className="flex flex-col items-center"
                        >
                            <span className="text-sm mb-2">
                                Scroll to explore
                            </span>
                            <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center">
                                <motion.div
                                    animate={{ y: [0, 12, 0] }}
                                    transition={{
                                        repeat: Infinity,
                                        duration: 2,
                                    }}
                                    className="w-1 h-3 bg-white/70 rounded-full mt-2"
                                />
                            </div>
                        </motion.div>
                    </motion.div>
                </div>
            </section>

            {/* Products Grid Section */}
            <section className="min-h-screen relative overflow-hidden py-20 z-10">
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
