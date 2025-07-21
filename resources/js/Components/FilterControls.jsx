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
    searchPlaceholder = "Search...",
    className = "",
    showSortBy = true,
}) => {
    const handleClearFilters = () => {
        if (setSearchTerm) setSearchTerm("");
        if (setSelectedCategory) setSelectedCategory("");
        if (setSortBy) setSortBy("newest");
    };

    const hasActiveFilters =
        searchTerm || selectedCategory || (sortBy && sortBy !== "newest");

    return (
        <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1, delay: 2 }}
            className={`${className || "max-w-4xl mx-auto"}`}
        >
            {/* Single Row Layout - Fixed Heights, No Scroll */}
            <div className="flex items-center justify-center gap-3 flex-wrap">
                {/* Search Input */}
                <div className="flex-1 min-w-[200px] max-w-[300px]">
                    <div className="relative">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={(e) =>
                                setSearchTerm && setSearchTerm(e.target.value)
                            }
                            placeholder={searchPlaceholder}
                            className="w-full h-12 px-4 pl-12 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-300"
                        />
                        <svg
                            className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-white/70"
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
                {categories && categories.length > 0 && (
                    <div className="min-w-[160px]">
                        <select
                            value={selectedCategory}
                            onChange={(e) =>
                                setSelectedCategory &&
                                setSelectedCategory(e.target.value)
                            }
                            className="w-full h-12 px-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50 appearance-none cursor-pointer"
                        >
                            <option value="" className="text-black">
                                All Categories
                            </option>
                            {categories.map((category) => (
                                <option
                                    key={category.id}
                                    value={category.id}
                                    className="text-black"
                                >
                                    {category.name}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                {/* Sort By Filter */}
                {showSortBy && setSortBy && (
                    <div className="min-w-[130px]">
                        <select
                            value={sortBy}
                            onChange={(e) => setSortBy(e.target.value)}
                            className="w-full h-12 px-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50 appearance-none cursor-pointer"
                        >
                            <option value="newest" className="text-black">
                                Newest
                            </option>
                            <option value="oldest" className="text-black">
                                Oldest
                            </option>
                            <option value="featured" className="text-black">
                                Featured
                            </option>
                            <option value="title" className="text-black">
                                A-Z
                            </option>
                            <option value="popular" className="text-black">
                                Popular
                            </option>
                            {/* Add more sort options based on context */}
                            {window.location.pathname.includes("/products") && (
                                <>
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
                                </>
                            )}
                        </select>
                    </div>
                )}

                {/* Additional Filters */}
                {additionalFilters.map((filter, index) => (
                    <div key={index} className="min-w-[120px]">
                        <div className="h-12 flex items-center">
                            {filter.component}
                        </div>
                    </div>
                ))}

                {/* Clear Filters Button */}
                {hasActiveFilters && (
                    <motion.button
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={handleClearFilters}
                        className="h-12 px-4 bg-red-500/20 hover:bg-red-500/30 backdrop-blur-md border border-red-400/30 rounded-lg text-white transition-all duration-300 whitespace-nowrap"
                    >
                        <div className="flex items-center space-x-2">
                            <svg
                                className="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                            <span className="hidden sm:inline">Clear</span>
                        </div>
                    </motion.button>
                )}
            </div>

            {/* Active Filters Display */}
            {hasActiveFilters && (
                <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: "auto" }}
                    className="mt-4 flex flex-wrap gap-2 justify-center"
                >
                    {searchTerm && (
                        <span className="inline-flex items-center px-3 py-1 bg-blue-500/20 text-blue-200 rounded-full text-sm">
                            Search: "{searchTerm}"
                            <button
                                onClick={() =>
                                    setSearchTerm && setSearchTerm("")
                                }
                                className="ml-2 hover:text-white"
                            >
                                ×
                            </button>
                        </span>
                    )}
                    {selectedCategory && (
                        <span className="inline-flex items-center px-3 py-1 bg-green-500/20 text-green-200 rounded-full text-sm">
                            Category:{" "}
                            {
                                categories.find(
                                    (c) => c.id === selectedCategory
                                )?.name
                            }
                            <button
                                onClick={() =>
                                    setSelectedCategory &&
                                    setSelectedCategory("")
                                }
                                className="ml-2 hover:text-white"
                            >
                                ×
                            </button>
                        </span>
                    )}
                    {sortBy && sortBy !== "newest" && (
                        <span className="inline-flex items-center px-3 py-1 bg-purple-500/20 text-purple-200 rounded-full text-sm">
                            Sort:{" "}
                            {sortBy
                                .replace("_", " ")
                                .replace(/\b\w/g, (l) => l.toUpperCase())}
                            <button
                                onClick={() => setSortBy && setSortBy("newest")}
                                className="ml-2 hover:text-white"
                            >
                                ×
                            </button>
                        </span>
                    )}
                </motion.div>
            )}
        </motion.div>
    );
};

export default FilterControls;
