import React, { useEffect, useState } from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";

export default function MainLayout({ children, title = "", description = "" }) {
    const { props } = usePage();
    const { village } = props;
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [scrollY, setScrollY] = useState(0);

    useEffect(() => {
        const handleScroll = () => setScrollY(window.scrollY);
        window.addEventListener("scroll", handleScroll);
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    const navigation = [
        { name: "Home", href: "/" },
        { name: "Places", href: "/places" },
        { name: "Products", href: "/products" },
        { name: "Articles", href: "/articles" },
        { name: "Gallery", href: "/gallery" },
    ];

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

            {/* Navigation */}
            <motion.header
                className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
                    scrollY > 50
                        ? "bg-black/90 backdrop-blur-md"
                        : "bg-transparent"
                }`}
                initial={{ y: -100 }}
                animate={{ y: 0 }}
                transition={{ duration: 0.6 }}
            >
                <nav className="container mx-auto px-6 py-4">
                    <div className="flex items-center justify-between">
                        <Link href="/" className="text-white font-bold text-xl">
                            {village?.name || "Smart Village"}
                        </Link>

                        {/* Desktop Navigation */}
                        <div className="hidden md:flex space-x-8">
                            {navigation.map((item) => (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className="text-white hover:text-green-400 transition-colors duration-300"
                                >
                                    {item.name}
                                </Link>
                            ))}
                        </div>

                        {/* Mobile Menu Button */}
                        <button
                            className="md:hidden text-white"
                            onClick={() => setIsMenuOpen(!isMenuOpen)}
                        >
                            <svg
                                className="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d={
                                        isMenuOpen
                                            ? "M6 18L18 6M6 6l12 12"
                                            : "M4 6h16M4 12h16M4 18h16"
                                    }
                                />
                            </svg>
                        </button>
                    </div>

                    {/* Mobile Navigation */}
                    <AnimatePresence>
                        {isMenuOpen && (
                            <motion.div
                                initial={{ opacity: 0, height: 0 }}
                                animate={{ opacity: 1, height: "auto" }}
                                exit={{ opacity: 0, height: 0 }}
                                className="md:hidden mt-4 bg-black/90 rounded-lg"
                            >
                                {navigation.map((item) => (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className="block px-4 py-2 text-white hover:text-green-400 transition-colors duration-300"
                                        onClick={() => setIsMenuOpen(false)}
                                    >
                                        {item.name}
                                    </Link>
                                ))}
                            </motion.div>
                        )}
                    </AnimatePresence>
                </nav>
            </motion.header>

            {/* Main Content */}
            <main>{children}</main>

            {/* Footer */}
            <footer className="bg-black text-white py-12">
                <div className="container mx-auto px-6">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div className="col-span-1 md:col-span-2">
                            <h3 className="text-2xl font-bold mb-4">
                                {village?.name || "Smart Village"}
                            </h3>
                            <p className="text-gray-400 mb-4">
                                {village?.description ||
                                    "Discover the beauty and culture of our village"}
                            </p>
                            {village?.phone_number && (
                                <p className="text-gray-400">
                                    Phone: {village.phone_number}
                                </p>
                            )}
                            {village?.email && (
                                <p className="text-gray-400">
                                    Email: {village.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <h4 className="text-lg font-semibold mb-4">
                                Quick Links
                            </h4>
                            <ul className="space-y-2">
                                {navigation.map((item) => (
                                    <li key={item.name}>
                                        <Link
                                            href={item.href}
                                            className="text-gray-400 hover:text-white transition-colors duration-300"
                                        >
                                            {item.name}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div>
                            <h4 className="text-lg font-semibold mb-4">
                                Visit
                            </h4>
                            {village?.address && (
                                <p className="text-gray-400 mb-2">
                                    {village.address}
                                </p>
                            )}
                            <p className="text-gray-400">Lombok Utara, NTB</p>
                        </div>
                    </div>

                    <div className="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                        <p>
                            &copy; {new Date().getFullYear()}{" "}
                            {village?.name || "Smart Village"}. All rights
                            reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </>
    );
}
