import React, { useEffect, useState } from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";

export default function MainLayout({ children, title = "", description = "" }) {
    const { props } = usePage();
    const { village } = props;
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [scrollY, setScrollY] = useState(0);
    const [isScrolled, setIsScrolled] = useState(false);

    // Enhanced scroll detection
    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;
            setScrollY(currentScrollY);
            setIsScrolled(currentScrollY > 50); // New scroll state
        };

        window.addEventListener("scroll", handleScroll, { passive: true });
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    // Village-specific navigation items
    const getNavigationItems = () => {
        const baseItems = [
            { name: "Home", href: "/", icon: "🏠" },
            { name: "Places", href: "/places", icon: "📍" },
            { name: "Products", href: "/products", icon: "🛍️" },
            { name: "Articles", href: "/articles", icon: "📖" },
            { name: "Gallery", href: "/gallery", icon: "📸" },
        ];

        // Add village-specific items if needed
        if (village?.settings?.show_events) {
            baseItems.push({ name: "Events", href: "/events", icon: "🎉" });
        }

        return baseItems;
    };

    const navigation = getNavigationItems();

    return (
        <>
            <Head>
                <title>
                    {title
                        ? `${title} - ${village?.name || "Smart Village"}`
                        : village?.name || "Smart Village"}
                </title>
                <meta
                    name="description"
                    content={
                        description ||
                        village?.description ||
                        "Welcome to our smart village"
                    }
                />
                <meta
                    name="viewport"
                    content="width=device-width, initial-scale=1"
                />
            </Head>

            {/* Enhanced Village-Aware Navigation */}
            <motion.header
                className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
                    isScrolled
                        ? "bg-black/95 backdrop-blur-xl border-b border-white/10 shadow-2xl"
                        : "bg-transparent"
                }`}
                initial={{ y: -100 }}
                animate={{ y: 0 }}
                transition={{ duration: 0.8, type: "spring" }}
            >
                <nav className="container mx-auto px-6 py-4">
                    <div className="flex items-center justify-between">
                        {/* Enhanced Village Branding */}
                        <Link
                            href="/"
                            className="flex items-center space-x-3 group"
                        >
                            <motion.div
                                whileHover={{ rotate: 360, scale: 1.1 }}
                                transition={{ duration: 0.6 }}
                                className="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center"
                            >
                                {village?.image_url ? (
                                    <img
                                        src={village.image_url}
                                        alt={village.name}
                                        className="w-full h-full rounded-full object-cover"
                                    />
                                ) : (
                                    <span className="text-white text-sm">
                                        🏘️
                                    </span>
                                )}
                            </motion.div>
                            <div className="flex flex-col">
                                <motion.span
                                    className="text-white font-bold text-xl group-hover:text-green-400 transition-colors duration-300"
                                    whileHover={{ scale: 1.05 }}
                                >
                                    {village?.name || "Smart Village"}
                                </motion.span>
                                {village?.description && (
                                    <span className="text-white/60 text-xs hidden sm:block">
                                        {village.description.substring(0, 30)}
                                        ...
                                    </span>
                                )}
                            </div>
                        </Link>

                        {/* Desktop Navigation with Enhanced Styling */}
                        <div className="hidden md:flex items-center space-x-1">
                            {navigation.map((item, index) => (
                                <motion.div
                                    key={item.name}
                                    initial={{ opacity: 0, y: -20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: index * 0.1 + 0.3 }}
                                >
                                    <Link
                                        href={item.href}
                                        className="relative px-4 py-2 rounded-lg text-white/80 hover:text-white transition-all duration-300 group flex items-center space-x-2"
                                    >
                                        <span className="text-sm">
                                            {item.icon}
                                        </span>
                                        <span>{item.name}</span>

                                        {/* Hover background */}
                                        <motion.div
                                            className="absolute inset-0 bg-white/10 rounded-lg"
                                            initial={{ scale: 0, opacity: 0 }}
                                            whileHover={{
                                                scale: 1,
                                                opacity: 1,
                                            }}
                                            transition={{ duration: 0.2 }}
                                        />

                                        {/* Active indicator */}
                                        <motion.div
                                            className="absolute bottom-0 left-1/2 w-1 h-1 bg-green-400 rounded-full"
                                            initial={{ scale: 0, x: "-50%" }}
                                            whileHover={{ scale: 1 }}
                                            transition={{ duration: 0.2 }}
                                        />
                                    </Link>
                                </motion.div>
                            ))}

                            {/* Village Info Button */}
                            <motion.button
                                whileHover={{ scale: 1.05 }}
                                whileTap={{ scale: 0.95 }}
                                className="ml-4 px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-full text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-300"
                            >
                                ℹ️ Info
                            </motion.button>
                        </div>

                        {/* Enhanced Mobile Menu Button */}
                        <motion.button
                            className="md:hidden relative w-10 h-10 flex items-center justify-center"
                            onClick={() => setIsMenuOpen(!isMenuOpen)}
                            whileTap={{ scale: 0.9 }}
                        >
                            <motion.div
                                animate={{ rotate: isMenuOpen ? 180 : 0 }}
                                transition={{ duration: 0.3 }}
                            >
                                {isMenuOpen ? (
                                    <motion.div
                                        initial={{ rotate: -90 }}
                                        animate={{ rotate: 0 }}
                                        className="text-white text-xl"
                                    >
                                        ✕
                                    </motion.div>
                                ) : (
                                    <motion.div
                                        initial={{ rotate: 90 }}
                                        animate={{ rotate: 0 }}
                                        className="text-white text-xl"
                                    >
                                        ☰
                                    </motion.div>
                                )}
                            </motion.div>
                        </motion.button>
                    </div>

                    {/* Enhanced Mobile Navigation */}
                    <AnimatePresence>
                        {isMenuOpen && (
                            <motion.div
                                initial={{ opacity: 0, height: 0, y: -20 }}
                                animate={{ opacity: 1, height: "auto", y: 0 }}
                                exit={{ opacity: 0, height: 0, y: -20 }}
                                transition={{ duration: 0.4, ease: "easeOut" }}
                                className="md:hidden mt-6 bg-black/90 backdrop-blur-md rounded-2xl border border-white/10 overflow-hidden"
                            >
                                <div className="p-4 space-y-2">
                                    {navigation.map((item, index) => (
                                        <motion.div
                                            key={item.name}
                                            initial={{ opacity: 0, x: -20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            transition={{ delay: index * 0.1 }}
                                        >
                                            <Link
                                                href={item.href}
                                                className="flex items-center space-x-3 px-4 py-3 text-white hover:text-green-400 hover:bg-white/10 rounded-lg transition-all duration-300"
                                                onClick={() =>
                                                    setIsMenuOpen(false)
                                                }
                                            >
                                                <span className="text-lg">
                                                    {item.icon}
                                                </span>
                                                <span className="font-medium">
                                                    {item.name}
                                                </span>
                                            </Link>
                                        </motion.div>
                                    ))}

                                    {/* Mobile Village Info */}
                                    <motion.div
                                        initial={{ opacity: 0, x: -20 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        transition={{
                                            delay: navigation.length * 0.1,
                                        }}
                                        className="border-t border-white/10 pt-4 mt-4"
                                    >
                                        <div className="px-4 py-2">
                                            <div className="text-green-400 font-semibold mb-2">
                                                Village Info
                                            </div>
                                            {village?.phone_number && (
                                                <div className="text-white/70 text-sm mb-1">
                                                    📞 {village.phone_number}
                                                </div>
                                            )}
                                            {village?.email && (
                                                <div className="text-white/70 text-sm">
                                                    ✉️ {village.email}
                                                </div>
                                            )}
                                        </div>
                                    </motion.div>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </nav>
            </motion.header>

            {/* Main Content */}
            <main>{children}</main>

            {/* Enhanced Village-Aware Footer */}
            <footer className="bg-gradient-to-t from-black via-gray-900 to-black text-white py-16 relative overflow-hidden">
                {/* Background decorations */}
                <div className="absolute inset-0 opacity-5">
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 50,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-20 left-20 w-32 h-32 border border-white rounded-full"
                    />
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 60,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute bottom-20 right-20 w-24 h-24 border border-white"
                    />
                </div>

                <div className="container mx-auto px-6 relative z-10">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {/* Village Information */}
                        <motion.div
                            className="col-span-1 md:col-span-2"
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                        >
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                    {village?.image_url ? (
                                        <img
                                            src={village.image_url}
                                            alt={village.name}
                                            className="w-full h-full rounded-full object-cover"
                                        />
                                    ) : (
                                        <span className="text-white">🏘️</span>
                                    )}
                                </div>
                                <h3 className="text-2xl font-bold">
                                    {village?.name || "Smart Village"}
                                </h3>
                            </div>

                            <p className="text-gray-400 mb-6 leading-relaxed">
                                {village?.description ||
                                    "Discover the beauty and culture of our village"}
                            </p>

                            <div className="space-y-3">
                                {village?.phone_number && (
                                    <motion.div
                                        className="flex items-center space-x-3 text-gray-400 hover:text-white transition-colors duration-300"
                                        whileHover={{ x: 5 }}
                                    >
                                        <span>📞</span>
                                        <span>{village.phone_number}</span>
                                    </motion.div>
                                )}
                                {village?.email && (
                                    <motion.div
                                        className="flex items-center space-x-3 text-gray-400 hover:text-white transition-colors duration-300"
                                        whileHover={{ x: 5 }}
                                    >
                                        <span>✉️</span>
                                        <span>{village.email}</span>
                                    </motion.div>
                                )}
                                {village?.address && (
                                    <motion.div
                                        className="flex items-center space-x-3 text-gray-400 hover:text-white transition-colors duration-300"
                                        whileHover={{ x: 5 }}
                                    >
                                        <span>📍</span>
                                        <span>{village.address}</span>
                                    </motion.div>
                                )}
                            </div>
                        </motion.div>

                        {/* Quick Links */}
                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                        >
                            <h4 className="text-lg font-semibold mb-6 text-green-400">
                                Quick Links
                            </h4>
                            <ul className="space-y-3">
                                {navigation.map((item, index) => (
                                    <motion.li
                                        key={item.name}
                                        whileHover={{ x: 5 }}
                                        transition={{ duration: 0.2 }}
                                    >
                                        <Link
                                            href={item.href}
                                            className="flex items-center space-x-2 text-gray-400 hover:text-white transition-colors duration-300"
                                        >
                                            <span className="text-sm">
                                                {item.icon}
                                            </span>
                                            <span>{item.name}</span>
                                        </Link>
                                    </motion.li>
                                ))}
                            </ul>
                        </motion.div>

                        {/* Village Stats */}
                        <motion.div
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.4 }}
                        >
                            <h4 className="text-lg font-semibold mb-6 text-green-400">
                                Village Stats
                            </h4>
                            <div className="space-y-4">
                                {[
                                    {
                                        label: "Established",
                                        value: village?.established_at
                                            ? new Date(
                                                  village.established_at
                                              ).getFullYear()
                                            : "Historic",
                                        icon: "🏗️",
                                    },
                                    {
                                        label: "Tourism Sites",
                                        value: "12+",
                                        icon: "🏞️",
                                    },
                                    {
                                        label: "Local Businesses",
                                        value: "25+",
                                        icon: "🏪",
                                    },
                                    {
                                        label: "Community",
                                        value: "Strong",
                                        icon: "👥",
                                    },
                                ].map((stat, index) => (
                                    <motion.div
                                        key={stat.label}
                                        className="flex items-center justify-between text-sm"
                                        whileHover={{ scale: 1.05 }}
                                        transition={{ duration: 0.2 }}
                                    >
                                        <span className="text-gray-400 flex items-center space-x-2">
                                            <span>{stat.icon}</span>
                                            <span>{stat.label}</span>
                                        </span>
                                        <span className="text-white font-medium">
                                            {stat.value}
                                        </span>
                                    </motion.div>
                                ))}
                            </div>
                        </motion.div>
                    </div>

                    {/* Footer Bottom */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 0.8, delay: 0.6 }}
                        className="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between"
                    >
                        <p className="text-gray-400 text-sm">
                            &copy; {new Date().getFullYear()}{" "}
                            {village?.name || "Smart Village"}.
                            <span className="ml-1">
                                Made with ❤️ for our community
                            </span>
                        </p>

                        <div className="flex items-center space-x-4 mt-4 md:mt-0">
                            <motion.div
                                className="text-xs text-gray-500"
                                animate={{ opacity: [0.5, 1, 0.5] }}
                                transition={{ duration: 2, repeat: Infinity }}
                            >
                                🌱 Growing Together
                            </motion.div>
                        </div>
                    </motion.div>
                </div>
            </footer>
        </>
    );
}
