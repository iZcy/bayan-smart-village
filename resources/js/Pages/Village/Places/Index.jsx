// resources/js/Pages/Village/Places/Index.jsx
import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import HeroSection from "@/Components/HeroSection";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { PlaceCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";

const PlacesPage = ({ village, places, categories = [], filters = {} }) => {
    // Ensure we have valid data
    const placeData = places?.data || [];
    const filterData = filters || {};
    const categoryData = categories || [];

    const [filteredPlaces, setFilteredPlaces] = useState(placeData);
    const [searchTerm, setSearchTerm] = useState(filterData.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filterData.category || ""
    );
    const [selectedType, setSelectedType] = useState(filterData.type || "");

    useEffect(() => {
        let filtered = placeData;

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(
                (place) =>
                    place.name
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    place.description
                        ?.toLowerCase()
                        .includes(searchTerm.toLowerCase())
            );
        }

        // Filter by category
        if (selectedCategory) {
            filtered = filtered.filter(
                (place) => place.category?.id === selectedCategory
            );
        }

        // Filter by type
        if (selectedType) {
            filtered = filtered.filter(
                (place) => place.category?.type === selectedType
            );
        }

        setFilteredPlaces(filtered);
    }, [searchTerm, selectedCategory, selectedType, placeData]);

    const handleClearFilters = () => {
        setSearchTerm("");
        setSelectedCategory("");
        setSelectedType("");
    };

    // Type filter component
    const typeFilterComponent = (
        <select
            value={selectedType}
            onChange={(e) => setSelectedType(e.target.value)}
            className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
        >
            <option value="" className="text-black">
                All Types
            </option>
            <option value="service" className="text-black">
                Tourism
            </option>
            <option value="product" className="text-black">
                Businesses
            </option>
        </select>
    );

    return (
        <MainLayout title="Places">
            <Head title={`Places - ${village?.name}`} />

            {/* Hero Section */}
            <HeroSection
                title="Discover Places"
                subtitle={`Explore amazing locations and businesses in ${village?.name}`}
                backgroundGradient="from-teal-600 via-cyan-500 to-blue-700"
                enableParallax={true}
            >
                <FilterControls
                    searchTerm={searchTerm}
                    setSearchTerm={setSearchTerm}
                    selectedCategory={selectedCategory}
                    setSelectedCategory={setSelectedCategory}
                    categories={categoryData}
                    additionalFilters={[
                        { component: typeFilterComponent },
                        {
                            component: (
                                <button
                                    onClick={handleClearFilters}
                                    className="px-4 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-white hover:bg-white/30 transition-colors duration-300"
                                >
                                    Clear Filters
                                </button>
                            ),
                        },
                    ]}
                    searchPlaceholder="Search places..."
                    className="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4"
                />
            </HeroSection>

            {/* Places Grid Section */}
            <section className="py-20 bg-gradient-to-b from-blue-700 to-teal-900">
                <div className="container mx-auto px-6">
                    <SectionHeader
                        title="Place"
                        count={filteredPlaces.length}
                        gradientColor="from-cyan-400 to-blue-500"
                    />

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {filteredPlaces.map((place, index) => (
                            <PlaceCard
                                key={place.id}
                                place={place}
                                index={index}
                                village={village}
                            />
                        ))}
                    </div>

                    {filteredPlaces.length === 0 && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            className="text-center py-20"
                        >
                            <div className="text-6xl mb-4">🗺️</div>
                            <h3 className="text-2xl font-semibold text-white mb-2">
                                No Places Found
                            </h3>
                            <p className="text-gray-400">
                                Try adjusting your search or filters
                            </p>
                        </motion.div>
                    )}

                    {/* Pagination */}
                    {places?.last_page > 1 && (
                        <Pagination paginationData={places} theme="places" />
                    )}
                </div>
            </section>

            {/* Featured Categories */}
            <section className="py-20 bg-gradient-to-b from-teal-900 to-cyan-800">
                <div className="container mx-auto px-6">
                    <motion.h2
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-4xl font-bold text-white text-center mb-12"
                    >
                        Explore by Category
                    </motion.h2>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                        {categoryData?.slice(0, 8).map((category, index) => (
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
                                    {category.type === "service" ? "🏞️" : "🏪"}
                                </div>
                                <h3 className="text-white font-semibold text-lg">
                                    {category.name}
                                </h3>
                                <p className="text-gray-300 text-sm mt-1">
                                    {category.places_count || 0} places
                                </p>
                            </motion.button>
                        ))}
                    </div>
                </div>
            </section>

            {/* Stats Section */}
            <section className="py-20 bg-gradient-to-b from-cyan-800 to-blue-900">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="grid grid-cols-2 md:grid-cols-4 gap-8"
                    >
                        {[
                            {
                                label: "Total Places",
                                value: places?.total || 0,
                                icon: "📍",
                            },
                            {
                                label: "Tourism Sites",
                                value:
                                    categoryData?.filter(
                                        (c) => c.type === "service"
                                    ).length || 0,
                                icon: "🏞️",
                            },
                            {
                                label: "Businesses",
                                value:
                                    categoryData?.filter(
                                        (c) => c.type === "product"
                                    ).length || 0,
                                icon: "🏪",
                            },
                            {
                                label: "Categories",
                                value: categoryData?.length || 0,
                                icon: "📂",
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

export default PlacesPage;
