// resources/js/Pages/Village/Places/Index.jsx
import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import {
    motion,
    AnimatePresence,
    useScroll,
    useTransform,
} from "framer-motion";
import MainLayout from "@/Layouts/MainLayout";
import MediaBackground from "@/Components/MediaBackground";
import FilterControls from "@/Components/FilterControls";
import SectionHeader from "@/Components/SectionHeader";
import { PlaceCard } from "@/Components/Cards/Index";
import Pagination from "@/Components/Pagination";
import SlideshowBackground from "@/Components/SlideshowBackground";
import { useSlideshowData, slideshowConfigs } from "@/hooks/useSlideshowData";

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
    const { scrollY } = useScroll();

    // Prepare slideshow data using the custom hook
    const slideshowImages = useSlideshowData(placeData, slideshowConfigs.places);

    // Color overlay for Places sections - multiple scroll points for footer visibility
    const colorOverlay = useTransform(
        scrollY,
        [0, 500, 900, 1300],
        [
            "linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.5))", // Hero - darker for better card visibility
            "linear-gradient(to bottom, rgba(59,130,246,0.7), rgba(29,78,216,0.8))", // Places Grid - blue, darker for better card visibility
            "linear-gradient(to bottom, rgba(29,78,216,0.6), rgba(15,23,42,0.7))", // Mid transition
            "linear-gradient(to bottom, rgba(15,23,42,0.4), rgba(0,0,0,0.6))", // End fade to black for footer
        ]
    );

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

            {/* Media Background with blur for content sections */}
            <MediaBackground
                context="places"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                controlsId="places-media-controls"
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
                interval={slideshowConfigs.places.interval}
                transitionDuration={slideshowConfigs.places.transitionDuration}
                placeholderConfig={slideshowConfigs.places.placeholderConfig}
            />

            {/* Hero Section */}
            <section className="relative h-screen overflow-hidden z-10">
                {/* Content overlay for readability */}
                <div className="absolute inset-0 bg-black/40 z-5"></div>

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-20 flex-col gap-4">
                    <div className="max-w-4xl px-6">
                        {/* Breadcrumb */}
                        <motion.nav
                            className="mb-8"
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                        >
                            <div className="flex items-center justify-center space-x-2 text-white/70">
                                <Link
                                    href="/"
                                    className="hover:text-white transition-colors"
                                >
                                    {village.name}
                                </Link>
                                <span>/</span>
                                <span className="text-white">Places</span>
                            </div>
                        </motion.nav>

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

                        <FilterControls
                            searchTerm={searchTerm}
                            setSearchTerm={setSearchTerm}
                            selectedCategory={selectedCategory}
                            setSelectedCategory={setSelectedCategory}
                            categories={categoryData}
                            additionalFilters={[
                                { component: typeFilterComponent },
                            ]}
                            searchPlaceholder="Search places..."
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

            {/* Places Grid Section */}
            <section className="min-h-screen relative overflow-hidden py-20 z-10">
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
        </MainLayout>
    );
};

export default PlacesPage;
