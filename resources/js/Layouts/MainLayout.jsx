// resources/js/Layouts/MainLayout.jsx
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
            setIsScrolled(currentScrollY > 50);
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

    // Helper function to check if current path matches href
    const isCurrentPath = (href) => {
        if (href === "/") {
            return window.location.pathname === "/";
        }
        return window.location.pathname.startsWith(href);
    };

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
                                className="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center overflow-hidden"
                            >
                                {village?.image_url ? (
                                    <img
                                        src={village.image_url}
                                        alt={village.name}
                                        className="w-full h-full object-cover"
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
                            {navigation.map((item, index) => {
                                const isCurrent = isCurrentPath(item.href);
                                return (
                                    <motion.div
                                        key={item.name}
                                        initial={{ opacity: 0, y: -20 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{
                                            delay: index * 0.1 + 0.3,
                                        }}
                                    >
                                        <Link
                                            href={item.href}
                                            className={`relative px-4 py-2 rounded-lg transition-all duration-300 group flex items-center space-x-2 ${
                                                isCurrent
                                                    ? "text-green-400 bg-white/10"
                                                    : "text-white/80 hover:text-white"
                                            }`}
                                        >
                                            <span className="text-sm">
                                                {item.icon}
                                            </span>
                                            <span>{item.name}</span>

                                            {/* Hover background */}
                                            <motion.div
                                                className="absolute inset-0 bg-white/10 rounded-lg"
                                                initial={{
                                                    scale: 0,
                                                    opacity: 0,
                                                }}
                                                whileHover={{
                                                    scale: 1,
                                                    opacity: isCurrent ? 0 : 1,
                                                }}
                                                transition={{ duration: 0.2 }}
                                            />

                                            {/* Active indicator */}
                                            {isCurrent && (
                                                <motion.div
                                                    layoutId="active-nav"
                                                    className="absolute bottom-0 left-1/2 w-1 h-1 bg-green-400 rounded-full"
                                                    style={{ x: "-50%" }}
                                                />
                                            )}
                                        </Link>
                                    </motion.div>
                                );
                            })}
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
                                    {navigation.map((item, index) => {
                                        const isCurrent = isCurrentPath(
                                            item.href
                                        );
                                        return (
                                            <motion.div
                                                key={item.name}
                                                initial={{ opacity: 0, x: -20 }}
                                                animate={{ opacity: 1, x: 0 }}
                                                transition={{
                                                    delay: index * 0.1,
                                                }}
                                            >
                                                <Link
                                                    href={item.href}
                                                    className={`flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 ${
                                                        isCurrent
                                                            ? "text-green-400 bg-white/20"
                                                            : "text-white hover:text-green-400 hover:bg-white/10"
                                                    }`}
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
                                                    {isCurrent && (
                                                        <span className="ml-auto text-xs">
                                                            •
                                                        </span>
                                                    )}
                                                </Link>
                                            </motion.div>
                                        );
                                    })}

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

            {/* Enhanced Village-Aware Footer - More efficient spacing */}
            <footer className="bg-black text-white py-12 md:py-16 relative overflow-hidden">
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

                <div className="container mx-auto px-4 md:px-6 relative z-10">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                        {/* Village Information */}
                        <motion.div
                            className="col-span-1"
                            initial={{ opacity: 0, y: 30 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                        >
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center overflow-hidden">
                                    {village?.image_url ? (
                                        <img
                                            src={village.image_url}
                                            alt={village.name}
                                            className="w-full h-full object-cover"
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
                                        <a href={`tel:${village.phone_number}`}>
                                            {village.phone_number}
                                        </a>
                                    </motion.div>
                                )}
                                {village?.email && (
                                    <motion.div
                                        className="flex items-center space-x-3 text-gray-400 hover:text-white transition-colors duration-300"
                                        whileHover={{ x: 5 }}
                                    >
                                        <span>✉️</span>
                                        <a href={`mailto:${village.email}`}>
                                            {village.email}
                                        </a>
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
                            className="col-span-1 align-self-start"
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
                    </div>

                    {/* Footer Bottom - Compact and efficient */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        transition={{ duration: 0.8, delay: 0.6 }}
                        className="border-t border-gray-800 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0"
                    >
                        <div className="text-center sm:text-left">
                            <p className="text-gray-400 text-xs leading-relaxed">
                                &copy; {new Date().getFullYear()}{" "}
                                {village?.name || "Smart Village"} • Made with
                                ❤️ for our community
                            </p>
                            <p className="text-gray-500 text-xs mt-1">
                                Powered by{" "}
                                <a
                                    href="https://www.claude.ai"
                                    className="text-orange-400 hover:underline"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Claude
                                </a>{" "}
                                and{" "}
                                <a
                                    href="https://www.openai.com/chatgpt"
                                    className="text-blue-400 hover:underline"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    ChatGPT
                                </a>
                            </p>
                        </div>
                        <motion.div
                            className="text-xs text-gray-500 flex items-center"
                            animate={{ opacity: [0.5, 1, 0.5] }}
                            transition={{ duration: 2, repeat: Infinity }}
                        >
                            🌱 Growing Together
                        </motion.div>
                    </motion.div>
                </div>
            </footer>
        </>
    );
}
