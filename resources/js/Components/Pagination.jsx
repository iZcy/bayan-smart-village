// resources/js/Components/Pagination.jsx
import React from "react";
import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";

const Pagination = ({
    paginationData,
    baseUrl = "",
    theme = "default", // default, products, articles, places, gallery
}) => {
    const { current_page, last_page, per_page, total } = paginationData;

    if (last_page <= 1) return null;

    const getThemeColors = () => {
        switch (theme) {
            case "products":
                return {
                    active: "bg-green-500 text-white",
                    inactive: "bg-white/10 text-gray-300 hover:bg-white/20",
                    button: "bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20",
                };
            case "articles":
                return {
                    active: "bg-blue-500 text-white",
                    inactive: "bg-white/10 text-gray-300 hover:bg-white/20",
                    button: "bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20",
                };
            case "places":
                return {
                    active: "bg-cyan-500 text-white",
                    inactive: "bg-white/10 text-gray-300 hover:bg-white/20",
                    button: "bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20",
                };
            case "gallery":
                return {
                    active: "bg-purple-500 text-white",
                    inactive: "bg-white/10 text-gray-300 hover:bg-white/20",
                    button: "bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20",
                };
            default:
                return {
                    active: "bg-blue-500 text-white",
                    inactive: "bg-white/10 text-gray-300 hover:bg-white/20",
                    button: "bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20",
                };
        }
    };

    const colors = getThemeColors();

    // Generate page numbers to show
    const getPageNumbers = () => {
        const delta = 2; // Number of pages to show around current page
        const range = [];
        const rangeWithDots = [];

        for (
            let i = Math.max(2, current_page - delta);
            i <= Math.min(last_page - 1, current_page + delta);
            i++
        ) {
            range.push(i);
        }

        if (current_page - delta > 2) {
            rangeWithDots.push(1, "...");
        } else {
            rangeWithDots.push(1);
        }

        rangeWithDots.push(...range);

        if (current_page + delta < last_page - 1) {
            rangeWithDots.push("...", last_page);
        } else if (last_page > 1) {
            rangeWithDots.push(last_page);
        }

        return rangeWithDots;
    };

    const buildUrl = (page) => {
        const url = new URL(window.location);
        url.searchParams.set("page", page);
        return url.pathname + url.search;
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            transition={{ duration: 0.8 }}
            className="flex justify-center items-center mt-16 space-x-4"
        >
            {/* Previous Button */}
            {current_page > 1 && (
                <Link
                    href={buildUrl(current_page - 1)}
                    className={`px-6 py-3 ${colors.button} transition-colors duration-300`}
                >
                    ← Previous
                </Link>
            )}

            {/* Page Numbers */}
            <div className="flex items-center space-x-2">
                {getPageNumbers().map((page, index) => {
                    if (page === "...") {
                        return (
                            <span
                                key={`dots-${index}`}
                                className="px-3 py-2 text-gray-400"
                            >
                                ...
                            </span>
                        );
                    }

                    return (
                        <Link
                            key={page}
                            href={buildUrl(page)}
                            className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 ${
                                page === current_page
                                    ? colors.active
                                    : colors.inactive
                            }`}
                        >
                            {page}
                        </Link>
                    );
                })}
            </div>

            {/* Next Button */}
            {current_page < last_page && (
                <Link
                    href={buildUrl(current_page + 1)}
                    className={`px-6 py-3 ${colors.button} transition-colors duration-300`}
                >
                    Next →
                </Link>
            )}

            {/* Results Info */}
            <div className="hidden md:block ml-8 text-sm text-white">
                Showing {(current_page - 1) * per_page + 1} to{" "}
                {Math.min(current_page * per_page, total)} of {total} results
            </div>
        </motion.div>
    );
};

export default Pagination;
