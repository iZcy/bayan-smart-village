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
    const [currentSlide, setCurrentSlide] = useState(0);
    const { scrollY } = useScroll();

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

    // Get featured product images for slideshow
    const featuredImages = productData
        .slice(0, 5)
        .filter((product) => product && product.name) // Ensure product exists and has a name
        .map((product) => {
            // Debug logging for first product to understand structure
            if (product === productData[0]) {
                console.log("Product structure:", {
                    name: product.name,
                    primary_image_url: product.primary_image_url,
                    images: product.images,
                    main_image: product.main_image,
                    image_url: product.image_url,
                });
            }

            return {
                id: product.id,
                image_url:
                    product.primary_image_url ||
                    product.image_url ||
                    product.images?.[0]?.image_url ||
                    product.main_image ||
                    null,
                title: product.name,
                subtitle: product.short_description || "Quality local product",
            };
        });

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

    // Auto-advance slideshow
    useEffect(() => {
        if (featuredImages.length > 1) {
            const interval = setInterval(() => {
                setCurrentSlide((prev) => (prev + 1) % featuredImages.length);
            }, 6000);
            return () => clearInterval(interval);
        }
    }, [featuredImages.length]);

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

            {/* Fixed Hero Background */}
            <div className="fixed inset-0 z-0">
                {/* Slideshow Background */}
                {featuredImages.length > 0 && (
                    <div className="absolute inset-0">
                        <AnimatePresence>
                            <motion.div
                                key={currentSlide}
                                initial={{ opacity: 0, scale: 1.1 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 1.1 }}
                                transition={{
                                    duration: 1.5,
                                    ease: "easeInOut",
                                }}
                                className="absolute inset-0"
                            >
                                {featuredImages[currentSlide]?.image_url ? (
                                    <img
                                        src={
                                            featuredImages[currentSlide]
                                                .image_url
                                        }
                                        alt={featuredImages[currentSlide].title}
                                        className="w-full h-full object-cover"
                                        onError={(e) => {
                                            e.target.style.display = "none";
                                            e.target.nextElementSibling.style.display =
                                                "flex";
                                        }}
                                    />
                                ) : null}
                                {/* Fallback placeholder */}
                                <div
                                    className="w-full h-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white"
                                    style={{
                                        display: featuredImages[currentSlide]
                                            ?.image_url
                                            ? "none"
                                            : "flex",
                                    }}
                                >
                                    <div className="text-center">
                                        <span className="text-6xl mb-4 block">
                                            📦
                                        </span>
                                        <h3 className="text-2xl font-bold mb-2">
                                            {
                                                featuredImages[currentSlide]
                                                    ?.title
                                            }
                                        </h3>
                                        <p className="text-lg opacity-90">
                                            {
                                                featuredImages[currentSlide]
                                                    ?.subtitle
                                            }
                                        </p>
                                    </div>
                                </div>
                            </motion.div>
                        </AnimatePresence>

                        {/* Slideshow indicators */}
                        <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex gap-2 z-30">
                            {featuredImages.map((_, index) => (
                                <button
                                    key={index}
                                    onClick={() => setCurrentSlide(index)}
                                    className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                        index === currentSlide
                                            ? "bg-white scale-125"
                                            : "bg-white/50 hover:bg-white/75"
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden z-10">
                {/* Content overlay for readability */}
                <div className="absolute inset-0 bg-black/40 z-5"></div>

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-20">
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
                </div>

                {/* Scroll Indicator */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 2, duration: 1 }}
                    className="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white z-30"
                >
                    <motion.div
                        animate={{ y: [0, 10, 0] }}
                        transition={{ repeat: Infinity, duration: 2 }}
                        className="flex flex-col items-center"
                    >
                        <span className="text-sm mb-2">Scroll to explore</span>
                        <div className="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center">
                            <motion.div
                                animate={{ y: [0, 12, 0] }}
                                transition={{ repeat: Infinity, duration: 2 }}
                                className="w-1 h-3 bg-white/70 rounded-full mt-2"
                            />
                        </div>
                    </motion.div>
                </motion.div>
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
