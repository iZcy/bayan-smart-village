// resources/js/Components/FilterControls.jsx
import React from "react";
import { motion } from "framer-motion";

const FilterControls = ({
    searchTerm,
    setSearchTerm,
    selectedCategory,
    setSelectedCategory,
    categories = [],
    sortBy,
    setSortBy,
    additionalFilters = [],
    onClearFilters,
    className = "max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4",
    searchPlaceholder = "Search...",
}) => {
    const handleClearFilters = () => {
        setSearchTerm("");
        setSelectedCategory("");
        if (setSortBy) setSortBy("featured");
        additionalFilters.forEach((filter) => {
            if (filter.setValue) {
                filter.setValue("");
            }
        });
        if (onClearFilters) onClearFilters();
    };

    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.8, delay: 1.5 }}
            className={className}
        >
            {/* Search */}
            <div className="relative">
                <input
                    type="text"
                    placeholder={searchPlaceholder}
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full px-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50"
                />
                <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg
                        className="w-5 h-5 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
            </div>

            {/* Category Filter */}
            <select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
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

            {/* Sort or Additional Filter */}
            {sortBy !== undefined ? (
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
                    <option value="newest" className="text-black">
                        Newest
                    </option>
                    <option value="oldest" className="text-black">
                        Oldest
                    </option>
                </select>
            ) : additionalFilters.length > 0 ? (
                additionalFilters[0].component
            ) : (
                <button
                    onClick={handleClearFilters}
                    className="px-4 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-white hover:bg-white/30 transition-colors duration-300"
                >
                    Clear Filters
                </button>
            )}

            {/* Additional filters if more than one */}
            {additionalFilters.length > 1 && (
                <>
                    {additionalFilters.slice(1).map((filter, index) => (
                        <div key={index}>{filter.component}</div>
                    ))}
                </>
            )}

            {/* Clear button if we have sort */}
            {sortBy !== undefined && (
                <button
                    onClick={handleClearFilters}
                    className="px-4 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-lg text-white hover:bg-white/30 transition-colors duration-300"
                >
                    Clear Filters
                </button>
            )}
        </motion.div>
    );
};

export default FilterControls;
