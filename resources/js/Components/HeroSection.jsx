// resources/js/Components/HeroSection.jsx
import React from "react";
import { motion, useScroll, useTransform } from "framer-motion";

const HeroSection = ({
    title,
    subtitle,
    backgroundGradient = "from-blue-600 via-purple-500 to-pink-600",
    height = "h-screen",
    children,
    showScrollIndicator = true,
    parallax = false,
    enableParallax = false, // New prop to control parallax
}) => {
    // Create scroll values inside the component when parallax is enabled
    const { scrollY } = useScroll();
    const heroY =
        parallax || enableParallax
            ? useTransform(scrollY, [0, 800], [0, -200])
            : null;

    return (
        <section className={`relative ${height} overflow-hidden`}>
            {/* Animated overlay */}
            <div className="absolute inset-0 bg-black/30" />

            {/* Hero Content */}
            <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                <div className="max-w-4xl px-6">
                    <motion.h1
                        initial={{ opacity: 0, y: 50 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 1, delay: 0.5 }}
                        className="text-6xl md:text-8xl font-bold text-white mb-6"
                    >
                        {title}
                    </motion.h1>
                    {subtitle && (
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            {subtitle}
                        </motion.p>
                    )}
                    <div className="text-white text-lg md:text-xl flex justify-center">
                        {children}
                    </div>
                </div>
            </div>

            {/* Scroll Indicator */}
            {showScrollIndicator && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 2, duration: 1 }}
                    className="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white"
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
            )}
        </section>
    );
};

export default HeroSection;
