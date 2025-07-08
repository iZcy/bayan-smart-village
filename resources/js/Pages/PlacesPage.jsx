import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";

const PlacesPage = ({ village, places, categories, filters }) => {
    const [filteredPlaces, setFilteredPlaces] = useState(places.data);
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [selectedCategory, setSelectedCategory] = useState(
        filters.category || ""
    );
    const [selectedType, setSelectedType] = useState(filters.type || "");
    const { scrollY } = useScroll();

    // Parallax effects
    const heroY = useTransform(scrollY, [0, 500], [0, -150]);
    const overlayOpacity = useTransform(scrollY, [0, 300], [0.3, 0.7]);

    useEffect(() => {
        let filtered = places.data;

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(
                (place) =>
                    place.name
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    place.description
                        .toLowerCase()
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
    }, [searchTerm, selectedCategory, selectedType, places.data]);

    return (
        <MainLayout title="Places">
            <Head title={`Places - ${village?.name}`} />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden">
                {/* Background with parallax */}
                <motion.div
                    style={{ y: heroY }}
                    className="absolute inset-0 bg-gradient-to-b from-teal-600 via-cyan-500 to-blue-700"
                >
                    <div className="absolute inset-0 opacity-20">
                        <svg viewBox="0 0 1200 600" className="w-full h-full">
                            <path
                                d="M0,600 L0,250 Q200,200 400,230 T800,180 Q1000,160 1200,200 L1200,600 Z"
                                fill="#0f4c75"
                            />
                            <path
                                d="M0,600 L0,320 Q300,270 600,290 T1200,270 L1200,600 Z"
                                fill="#1e6091"
                            />
                            <path
                                d="M0,600 L0,400 Q500,350 1000,370 T1200,350 L1200,600 Z"
                                fill="#2d74b0"
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
                            Discover Places
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            Explore amazing locations and businesses in{" "}
                            {village?.name}
                        </motion.p>

                        {/* Filter Controls */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.5 }}
                            className="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4"
                        >
                            {/* Search */}
                            <div className="relative">
                                <input
                                    type="text"
                                    placeholder="Search places..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50"
                                />
                            </div>

                            {/* Type Filter */}
                            <select
                                value={selectedType}
                                onChange={(e) =>
                                    setSelectedType(e.target.value)
                                }
                                className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                            >
                                <option value="" className="text-black">
                                    All Types
                                </option>
                                <option value="tourism" className="text-black">
                                    Tourism
                                </option>
                                <option value="sme" className="text-black">
                                    Businesses
                                </option>
                            </select>

                            {/* Category Filter */}
                            <select
                                value={selectedCategory}
                                onChange={(e) =>
                                    setSelectedCategory(e.target.value)
                                }
                                className="px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                            >
                                <option value="" className="text-black">
                                    All Categories
                                </option>
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

                            {/* Clear Filters */}
                            <button
                                onClick={() => {
                                    setSearchTerm("");
                                    setSelectedCategory("");
                                    setSelectedType("");
                                }}
                                className="px-4 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-white hover:bg-white/30 transition-colors duration-300"
                            >
                                Clear Filters
                            </button>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Places Grid Section */}
            <section className="py-20 bg-gradient-to-b from-blue-700 to-teal-900">
                <div className="container mx-auto px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="mb-12"
                    >
                        <h2 className="text-4xl font-bold text-white text-center mb-4">
                            {filteredPlaces.length} Place
                            {filteredPlaces.length !== 1 ? "s" : ""} Found
                        </h2>
                        <div className="w-24 h-1 bg-gradient-to-r from-cyan-400 to-blue-500 mx-auto"></div>
                    </motion.div>

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
                    {places.last_page > 1 && <Pagination places={places} />}
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
                                    {category.type === "tourism" ? "🏞️" : "🏪"}
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
                        <div className="text-center text-white">
                            <div className="text-4xl font-bold mb-2">
                                {places.total || 0}
                            </div>
                            <div className="text-gray-300">Total Places</div>
                        </div>
                        <div className="text-center text-white">
                            <div className="text-4xl font-bold mb-2">
                                {categories?.filter((c) => c.type === "tourism")
                                    .length || 0}
                            </div>
                            <div className="text-gray-300">Tourism Sites</div>
                        </div>
                        <div className="text-center text-white">
                            <div className="text-4xl font-bold mb-2">
                                {categories?.filter((c) => c.type === "sme")
                                    .length || 0}
                            </div>
                            <div className="text-gray-300">Businesses</div>
                        </div>
                        <div className="text-center text-white">
                            <div className="text-4xl font-bold mb-2">
                                {categories?.length || 0}
                            </div>
                            <div className="text-gray-300">Categories</div>
                        </div>
                    </motion.div>
                </div>
            </section>
        </MainLayout>
    );
};

const PlaceCard = ({ place, index, village }) => {
    const [ref, inView] = useInView({
        threshold: 0.1,
        triggerOnce: true,
    });

    const getTypeColor = () => {
        return place.category?.type === "tourism"
            ? "from-green-500 to-teal-600"
            : "from-blue-500 to-purple-600";
    };

    const getTypeIcon = () => {
        return place.category?.type === "tourism" ? "🏞️" : "🏪";
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
            <Link href={`/places/${place.id}`}>
                <div className="relative h-56 overflow-hidden">
                    {place.image_url ? (
                        <img
                            src={place.image_url}
                            alt={place.name}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    ) : (
                        <div
                            className={`w-full h-full bg-gradient-to-br ${getTypeColor()} flex items-center justify-center`}
                        >
                            <span className="text-4xl text-white">
                                {getTypeIcon()}
                            </span>
                        </div>
                    )}

                    {/* Overlay with info */}
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-300" />

                    {/* Type badge */}
                    <div
                        className={`absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-medium text-white ${
                            place.category?.type === "tourism"
                                ? "bg-green-500"
                                : "bg-blue-500"
                        }`}
                    >
                        {place.category?.type === "tourism"
                            ? "🏞️ Tourism"
                            : "🏪 Business"}
                    </div>

                    {/* Location badge */}
                    {place.address && (
                        <div className="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white px-2 py-1 rounded-full text-xs">
                            📍 {place.address.substring(0, 20)}...
                        </div>
                    )}
                </div>

                <div className="p-6">
                    <div className="flex items-center justify-between mb-3">
                        {place.category && (
                            <span
                                className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${
                                    place.category.type === "tourism"
                                        ? "bg-green-500/20 text-green-300 border-green-500/30"
                                        : "bg-blue-500/20 text-blue-300 border-blue-500/30"
                                }`}
                            >
                                {place.category.name}
                            </span>
                        )}
                        {place.phone_number && (
                            <span className="text-xs text-gray-400">
                                📞 Contact
                            </span>
                        )}
                    </div>

                    <h3 className="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors duration-300 line-clamp-2">
                        {place.name}
                    </h3>

                    <p className="text-gray-300 mb-4 line-clamp-3 leading-relaxed text-sm">
                        {place.description?.substring(0, 150)}
                        {place.description?.length > 150 && "..."}
                    </p>

                    <div className="flex items-center justify-between">
                        <div className="flex items-center text-sm text-gray-400">
                            {place.latitude && place.longitude ? (
                                <span className="flex items-center">
                                    <svg
                                        className="w-4 h-4 mr-1"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                    Located
                                </span>
                            ) : (
                                <span>📍 Address Available</span>
                            )}
                        </div>
                        <span className="group-hover:text-cyan-300 transition-colors duration-300 text-sm">
                            View Details →
                        </span>
                    </div>

                    {/* Custom Fields Preview */}
                    {place.custom_fields &&
                        Object.keys(place.custom_fields).length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-1">
                                {Object.entries(place.custom_fields)
                                    .slice(0, 2)
                                    .map(([key, value]) => (
                                        <span
                                            key={key}
                                            className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded"
                                        >
                                            {key}: {value}
                                        </span>
                                    ))}
                                {Object.keys(place.custom_fields).length >
                                    2 && (
                                    <span className="inline-block px-2 py-1 bg-white/10 text-gray-300 text-xs rounded">
                                        +
                                        {Object.keys(place.custom_fields)
                                            .length - 2}{" "}
                                        more
                                    </span>
                                )}
                            </div>
                        )}
                </div>
            </Link>
        </motion.div>
    );
};

const Pagination = ({ places }) => {
    const { current_page, last_page } = places;

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
                                    ? "bg-cyan-500 text-white"
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

export default PlacesPage;
