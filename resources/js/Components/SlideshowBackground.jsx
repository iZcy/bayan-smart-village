// resources/js/Components/SlideshowBackground.jsx
import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";

const SlideshowBackground = ({
    images = [],
    interval = 6000,
    transitionDuration = 1.5,
    className = "",
    showIndicators = true,
    autoAdvance = true,
    placeholderConfig = {
        show: true,
        gradient: "from-gray-600 to-gray-800",
        icon: "🖼️",
        text: "No images available"
    }
}) => {
    const [currentSlide, setCurrentSlide] = useState(0);

    // Auto-advance slideshow
    useEffect(() => {
        if (autoAdvance && images.length > 1) {
            const intervalId = setInterval(() => {
                setCurrentSlide((prev) => (prev + 1) % images.length);
            }, interval);
            return () => clearInterval(intervalId);
        }
    }, [images.length, interval, autoAdvance]);

    // Reset slide when images change
    useEffect(() => {
        setCurrentSlide(0);
    }, [images]);

    // Render placeholder when no images
    if (!images || images.length === 0) {
        return placeholderConfig.show ? (
            <div className={`fixed inset-0 z-0 ${className}`}>
                <div className={`w-full h-full bg-gradient-to-br ${placeholderConfig.gradient} flex flex-col items-center justify-center`}>
                    <motion.div
                        initial={{ opacity: 0, scale: 0.5 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 1, ease: "easeOut" }}
                        className="text-center text-white"
                    >
                        <motion.div
                            animate={{
                                rotate: [0, 5, -5, 0],
                                scale: [1, 1.1, 1],
                            }}
                            transition={{
                                duration: 4,
                                repeat: Infinity,
                                ease: "easeInOut",
                            }}
                            className="text-8xl mb-6 opacity-50"
                        >
                            {placeholderConfig.icon}
                        </motion.div>
                        <h3 className="text-2xl font-semibold opacity-70">
                            {placeholderConfig.text}
                        </h3>
                        <p className="text-lg opacity-50 mt-2">
                            Images will appear here when available
                        </p>
                    </motion.div>
                </div>
            </div>
        ) : null;
    }

    return (
        <div className={`fixed inset-0 z-0 ${className}`}>
            {/* Slideshow Background */}
            <div className="absolute inset-0">
                <AnimatePresence>
                    <motion.div
                        key={`slide-${currentSlide}-${images[currentSlide]?.id || currentSlide}`}
                        initial={{ opacity: 0, scale: 1.05 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 1.1 }}
                        transition={{
                            duration: transitionDuration,
                            ease: "easeInOut",
                        }}
                        className="absolute inset-0"
                    >
                        {images[currentSlide]?.image_url ? (
                            <img
                                src={images[currentSlide].image_url}
                                alt={images[currentSlide].title || `Slide ${currentSlide + 1}`}
                                className="w-full h-full object-cover"
                                onError={(e) => {
                                    // Fallback to placeholder on image error
                                    e.target.style.display = 'none';
                                }}
                            />
                        ) : (
                            // Individual slide fallback
                            <div className={`w-full h-full bg-gradient-to-br ${placeholderConfig.gradient} flex items-center justify-center`}>
                                <motion.div
                                    initial={{ opacity: 0, scale: 0.8 }}
                                    animate={{ 
                                        opacity: 0.4,
                                        scale: 1,
                                        rotate: [0, 5, -5, 0],
                                    }}
                                    transition={{
                                        opacity: { duration: transitionDuration * 0.5 },
                                        scale: { duration: transitionDuration * 0.5 },
                                        rotate: {
                                            duration: 4,
                                            repeat: Infinity,
                                            ease: "easeInOut",
                                        }
                                    }}
                                    className="text-6xl text-white"
                                >
                                    {placeholderConfig.icon}
                                </motion.div>
                            </div>
                        )}
                    </motion.div>
                </AnimatePresence>
            </div>

            {/* Slideshow Indicators */}
            {showIndicators && images.length > 1 && (
                <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex gap-2 z-30">
                    {images.map((_, index) => (
                        <motion.button
                            key={index}
                            onClick={() => setCurrentSlide(index)}
                            className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                index === currentSlide
                                    ? "bg-white scale-125 shadow-lg"
                                    : "bg-white/50 hover:bg-white/75"
                            }`}
                            whileHover={{ scale: index === currentSlide ? 1.25 : 1.1 }}
                            whileTap={{ scale: 0.9 }}
                            title={images[index]?.title || `Slide ${index + 1}`}
                        />
                    ))}
                </div>
            )}

            {/* Slide Information Overlay (Optional) */}
            <AnimatePresence>
                {images[currentSlide]?.title && (
                    <motion.div
                        key={`info-${currentSlide}`}
                        initial={{ opacity: 0, y: 30, scale: 0.9 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -30, scale: 0.9 }}
                        transition={{ 
                            duration: transitionDuration * 0.6,
                            delay: transitionDuration * 0.2,
                            ease: "easeOut"
                        }}
                        className="absolute bottom-20 left-1/2 transform -translate-x-1/2 text-center z-25 max-w-2xl px-6"
                    >
                        <h3 className="text-white text-lg font-semibold mb-1 drop-shadow-lg">
                            {images[currentSlide].title}
                        </h3>
                        {images[currentSlide].subtitle && (
                            <p className="text-white/80 text-sm drop-shadow-md">
                                {images[currentSlide].subtitle}
                            </p>
                        )}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
};

export default SlideshowBackground;