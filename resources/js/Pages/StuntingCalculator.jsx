import React, { useState, useRef, useEffect } from "react";
import { Head } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import { useInView } from "react-intersection-observer";

const StuntingCalculator = ({ whoData }) => {
    const [formData, setFormData] = useState({
        gender: "",
        height: "",
        birth_date: "",
    });
    const [result, setResult] = useState(null);
    const [isCalculating, setIsCalculating] = useState(false);
    const [errors, setErrors] = useState({});
    const { scrollY } = useScroll();

    // Removed parallax effects as we now use transparent background

    // Replace the setRefs function with separate refs
    const heroSectionRef = useRef(null);
    const calculatorSectionRef = useRef(null);
    const resultsSectionRef = useRef(null);

    // Keep intersection observer refs separate
    const { ref: heroRef, inView: heroInView } = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });
    const { ref: calculatorRef, inView: calculatorInView } = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });
    const { ref: resultsRef, inView: resultsInView } = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    // No background audio for stunting calculator page

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));

        // Clear error when user starts typing
        if (errors[name]) {
            setErrors((prev) => ({
                ...prev,
                [name]: "",
            }));
        }
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.gender) {
            newErrors.gender = "Silakan pilih jenis kelamin";
        }

        if (!formData.height) {
            newErrors.height = "Silakan masukkan tinggi badan";
        } else if (
            parseFloat(formData.height) < 10 ||
            parseFloat(formData.height) > 200
        ) {
            newErrors.height = "Tinggi badan harus antara 10-200 cm";
        }

        if (!formData.birth_date) {
            newErrors.birth_date = "Silakan pilih tanggal lahir";
        } else {
            const birthDate = new Date(formData.birth_date);
            const today = new Date();
            const fiveYearsAgo = new Date(
                today.getFullYear() - 5,
                today.getMonth(),
                today.getDate()
            );

            if (birthDate >= today) {
                newErrors.birth_date = "Tanggal lahir harus di masa lalu";
            } else if (birthDate < fiveYearsAgo) {
                newErrors.birth_date =
                    "Kalkulator ini untuk anak di bawah 5 tahun";
            }
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setIsCalculating(true);
        setResult(null);

        try {
            // Get CSRF token from multiple possible sources
            const getCSRFToken = () => {
                // Try meta tag first
                const metaToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");
                if (metaToken) return metaToken;

                // Try from cookie (if Laravel session is configured to use cookies)
                const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                if (cookieMatch) return decodeURIComponent(cookieMatch[1]);

                // Try from window object (if you set it there)
                if (window.Laravel && window.Laravel.csrfToken) {
                    return window.Laravel.csrfToken;
                }

                return null;
            };

            const csrfToken = getCSRFToken();

            const headers = {
                "Content-Type": "application/json",
                Accept: "application/json",
            };

            if (csrfToken) {
                headers["X-CSRF-TOKEN"] = csrfToken;
            }

            const response = await fetch("/stunting-calculator/calculate", {
                method: "POST",
                headers: headers,
                credentials: "same-origin", // Important for CSRF
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            // Replace the setTimeout scroll with:
            if (response.ok) {
                setResult(data);
                // Use the correct ref for scrolling
                setTimeout(() => {
                    resultsSectionRef.current?.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }, 100);
            } else {
                setErrors({ general: data.error || "Terjadi kesalahan" });
            }
        } catch (error) {
            console.error("Fetch error:", error);
            setErrors({ general: "Kesalahan jaringan. Silakan coba lagi." });
        } finally {
            setIsCalculating(false);
        }
    };

    const resetForm = () => {
        setFormData({
            gender: "",
            height: "",
            birth_date: "",
        });
        setResult(null);
        setErrors({});
    };

    const getStatusColor = (status) => {
        const colors = {
            severely_stunted: "from-red-500 to-red-600",
            stunted: "from-orange-500 to-orange-600",
            normal: "from-green-500 to-green-600",
            tall: "from-blue-500 to-blue-600",
        };
        return colors[status] || "from-gray-500 to-gray-600";
    };

    return (
        <>
            <Head title="Kalkulator Stunting - Standar Pertumbuhan WHO" />

            {/* Background Media with children image */}
            <div className="fixed inset-0 z-0">
                <img 
                    src="https://images.unsplash.com/photo-1502781252888-9143ba7f074e?q=80&w=871&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                    alt="Happy children representing healthy growth"
                    className="w-full h-full object-cover"
                />
                <div className="absolute inset-0 bg-black/20"></div>
            </div>
            
            {/* No background audio for stunting calculator */}

            {/* Hero Section - Transparent Background */}
            <section
                ref={heroSectionRef}
                className="relative h-screen overflow-hidden"
            >
                <div ref={heroRef}>
                    {/* Transparent overlay for better text readability */}
                    <div className="absolute inset-0 bg-black/20"></div>
                </div>

                {/* Floating elements */}
                {[...Array(6)].map((_, i) => (
                    <motion.div
                        key={i}
                        className="absolute text-white/20 text-3xl"
                        style={{
                            left: `${20 + Math.random() * 60}%`,
                            top: `${20 + Math.random() * 40}%`,
                        }}
                        animate={{
                            y: [0, -30, 0],
                            rotate: [0, 180, 360],
                            opacity: [0.2, 0.4, 0.2],
                        }}
                        transition={{
                            duration: 4 + Math.random() * 2,
                            repeat: Infinity,
                            delay: Math.random() * 2,
                        }}
                    >
                        {["👶", "📏", "📊", "⚖️", "🩺", "💚"][i]}
                    </motion.div>
                ))}

                {/* Hero Content */}
                <div className="absolute inset-0 flex items-center justify-center text-center z-10">
                    <div className="max-w-4xl px-6">
                        <motion.h1
                            initial={{ opacity: 0, y: 50 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 0.5 }}
                            className="text-6xl md:text-8xl font-bold text-white mb-6"
                        >
                            Kalkulator Stunting
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 1, delay: 1 }}
                            className="text-xl md:text-2xl text-gray-300 mb-8"
                        >
                            Standar Pertumbuhan WHO untuk Anak di Bawah 5 Tahun
                        </motion.p>

                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.8, delay: 1.4 }}
                            className="mt-8"
                        >
                            <button
                                onClick={() => {
                                    calculatorSectionRef.current?.scrollIntoView(
                                        {
                                            behavior: "smooth",
                                            block: "start",
                                        }
                                    );
                                }}
                                className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300"
                            >
                                Mulai Penilaian
                                <svg
                                    className="w-5 h-5 ml-2 group-hover:translate-y-1 transition-transform duration-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                    />
                                </svg>
                            </button>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Calculator Section */}
            <section
                ref={calculatorSectionRef}
                className="py-20 relative overflow-hidden"
            >
                {/* Glass backdrop */}
                <div className="absolute inset-0 backdrop-blur-sm bg-black/10" />
                <div ref={calculatorRef}>
                    {/* Background decorations */}
                    <div className="absolute inset-0 opacity-10">
                        <div className="absolute top-20 left-10 w-32 h-32 border border-white rounded-full" />
                        <div className="absolute bottom-20 right-20 w-24 h-24 bg-white/20 rounded-lg rotate-45" />
                    </div>

                    <div className="container mx-auto px-6 relative z-10">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="text-center mb-16"
                        >
                            <h2 className="text-5xl font-bold text-white mb-4">
                                Alat Penilaian Pertumbuhan
                            </h2>
                            <p className="text-xl text-white/80 max-w-3xl mx-auto">
                                Masukkan informasi anak Anda di bawah ini untuk
                                menilai status pertumbuhan sesuai standar WHO
                            </p>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="max-w-2xl mx-auto"
                        >
                            <form
                                onSubmit={handleSubmit}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 shadow-2xl"
                            >
                                {errors.general && (
                                    <motion.div
                                        initial={{ opacity: 0, scale: 0.8 }}
                                        animate={{ opacity: 1, scale: 1 }}
                                        className="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-lg text-red-200"
                                    >
                                        {errors.general}
                                    </motion.div>
                                )}

                                <div className="space-y-6">
                                    {/* Gender Selection */}
                                    <div>
                                        <label className="block text-white font-semibold mb-3">
                                            Jenis Kelamin Anak *
                                        </label>
                                        <div className="grid grid-cols-2 gap-4">
                                            {[
                                                {
                                                    value: "boys",
                                                    label: "Laki-laki",
                                                    icon: "👦",
                                                },
                                                {
                                                    value: "girls",
                                                    label: "Perempuan",
                                                    icon: "👧",
                                                },
                                            ].map((option) => (
                                                <motion.label
                                                    key={option.value}
                                                    whileHover={{ scale: 1.02 }}
                                                    whileTap={{ scale: 0.98 }}
                                                    className={`cursor-pointer p-4 rounded-lg border-2 transition-all duration-300 ${
                                                        formData.gender ===
                                                        option.value
                                                            ? "border-white bg-white/20"
                                                            : "border-white/30 hover:border-white/50"
                                                    }`}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="gender"
                                                        value={option.value}
                                                        checked={
                                                            formData.gender ===
                                                            option.value
                                                        }
                                                        onChange={
                                                            handleInputChange
                                                        }
                                                        className="sr-only"
                                                    />
                                                    <div className="text-center">
                                                        <div className="text-3xl mb-2">
                                                            {option.icon}
                                                        </div>
                                                        <div className="text-white font-medium">
                                                            {option.label}
                                                        </div>
                                                    </div>
                                                </motion.label>
                                            ))}
                                        </div>
                                        {errors.gender && (
                                            <p className="text-red-300 text-sm mt-2">
                                                {errors.gender}
                                            </p>
                                        )}
                                    </div>

                                    {/* Height Input */}
                                    <div>
                                        <label
                                            htmlFor="height"
                                            className="block text-white font-semibold mb-3"
                                        >
                                            Tinggi Badan (dalam sentimeter) *
                                        </label>
                                        <div className="relative">
                                            <input
                                                type="number"
                                                id="height"
                                                name="height"
                                                value={formData.height}
                                                onChange={handleInputChange}
                                                placeholder="mis. 75.5"
                                                step="0.1"
                                                min="10"
                                                max="200"
                                                className={`w-full px-4 py-4 bg-white/10 border-2 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-300 ${
                                                    errors.height
                                                        ? "border-red-500"
                                                        : "border-white/30"
                                                }`}
                                            />
                                            <div className="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/70">
                                                cm
                                            </div>
                                        </div>
                                        {errors.height && (
                                            <p className="text-red-300 text-sm mt-2">
                                                {errors.height}
                                            </p>
                                        )}
                                    </div>

                                    {/* Birth Date Input */}
                                    <div>
                                        <label
                                            htmlFor="birth_date"
                                            className="block text-white font-semibold mb-3"
                                        >
                                            Tanggal Lahir *
                                        </label>
                                        <input
                                            type="date"
                                            id="birth_date"
                                            name="birth_date"
                                            value={formData.birth_date}
                                            onChange={handleInputChange}
                                            max={
                                                new Date()
                                                    .toISOString()
                                                    .split("T")[0]
                                            }
                                            className={`w-full px-4 py-4 bg-white/10 border-2 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-300 ${
                                                errors.birth_date
                                                    ? "border-red-500"
                                                    : "border-white/30"
                                            }`}
                                        />
                                        {errors.birth_date && (
                                            <p className="text-red-300 text-sm mt-2">
                                                {errors.birth_date}
                                            </p>
                                        )}
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="flex gap-4 pt-4">
                                        <motion.button
                                            type="submit"
                                            disabled={isCalculating}
                                            whileHover={{
                                                scale: isCalculating ? 1 : 1.02,
                                            }}
                                            whileTap={{
                                                scale: isCalculating ? 1 : 0.98,
                                            }}
                                            className="flex-1 px-6 py-4 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300"
                                        >
                                            {isCalculating ? (
                                                <div className="flex items-center justify-center">
                                                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                                    Menghitung...
                                                </div>
                                            ) : (
                                                "Hitung Status Pertumbuhan"
                                            )}
                                        </motion.button>

                                        <motion.button
                                            type="button"
                                            onClick={resetForm}
                                            whileHover={{ scale: 1.02 }}
                                            whileTap={{ scale: 0.98 }}
                                            className="px-6 py-4 bg-white/20 text-white rounded-lg font-semibold hover:bg-white/30 transition-all duration-300"
                                        >
                                            Reset
                                        </motion.button>
                                    </div>
                                </div>
                            </form>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Results Section */}
            {result && (
                <section
                    ref={resultsSectionRef}
                    className="py-20 relative overflow-hidden"
                >
                    {/* Glass backdrop */}
                    <div className="absolute inset-0 backdrop-blur-sm bg-black/15" />
                    <div ref={resultsRef} className="container mx-auto px-6">
                        <motion.div
                            initial={{ opacity: 0, y: 50 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8 }}
                            className="max-w-4xl mx-auto"
                        >
                            <div className="text-center mb-12">
                                <h2 className="text-4xl font-bold text-white mb-4">
                                    Hasil Penilaian
                                </h2>
                                <p className="text-xl text-gray-300">
                                    Berdasarkan Standar Pertumbuhan WHO
                                </p>
                            </div>

                            {/* Status Card */}
                            <motion.div
                                initial={{ opacity: 0, scale: 0.8 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.6, delay: 0.2 }}
                                className="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 mb-8 shadow-2xl"
                            >
                                <div className="text-center mb-6">
                                    <div
                                        className={`inline-block px-6 py-3 rounded-full bg-gradient-to-r ${getStatusColor(
                                            result.status
                                        )} text-white font-bold text-lg mb-4`}
                                    >
                                        {result.interpretation.title}
                                    </div>
                                    <h3 className="text-2xl font-bold text-white mb-2">
                                        Skor Z Tinggi-menurut-Umur:{" "}
                                        {result.haz_score}
                                    </h3>
                                    <p className="text-gray-300">
                                        {result.interpretation.description}
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <div className="text-center p-4 bg-white/5 rounded-lg">
                                        <div className="text-2xl font-bold text-white">
                                            {result.age_months}
                                        </div>
                                        <div className="text-gray-300">
                                            Bulan
                                        </div>
                                    </div>
                                    <div className="text-center p-4 bg-white/5 rounded-lg">
                                        <div className="text-2xl font-bold text-white">
                                            {result.height} cm
                                        </div>
                                        <div className="text-gray-300">
                                            Tinggi Saat Ini
                                        </div>
                                    </div>
                                    <div className="text-center p-4 bg-white/5 rounded-lg">
                                        <div className="text-2xl font-bold text-white">
                                            {result.median_height} cm
                                        </div>
                                        <div className="text-gray-300">
                                            Median WHO
                                        </div>
                                    </div>
                                </div>

                                <div
                                    className={`p-4 rounded-lg border-l-4 border-${result.interpretation.color}-500 bg-${result.interpretation.color}-500/10`}
                                >
                                    <h4 className="font-semibold text-white mb-2">
                                        Rekomendasi:
                                    </h4>
                                    <p className="text-gray-300">
                                        {result.interpretation.recommendation}
                                    </p>
                                </div>
                            </motion.div>

                            {/* WHO Standards Reference */}
                            <motion.div
                                initial={{ opacity: 0, y: 30 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.6, delay: 0.4 }}
                                className="bg-white/15 rounded-xl p-6 border border-white/30 shadow-xl relative"
                            >
                                <h4 className="text-lg font-semibold text-white mb-4">
                                    Referensi Standar Pertumbuhan WHO (Umur:{" "}
                                    {result.age_months} bulan)
                                </h4>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    {Object.entries(result.standards).map(
                                        ([key, value]) => (
                                            <div
                                                key={key}
                                                className="text-center p-3 bg-white/5 rounded-lg"
                                            >
                                                <div className="text-white font-medium">
                                                    {value} cm
                                                </div>
                                                <div className="text-gray-400">
                                                    {key.toUpperCase()}
                                                </div>
                                            </div>
                                        )
                                    )}
                                </div>
                            </motion.div>

                            {/* Calculate Another */}
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ duration: 0.6, delay: 0.6 }}
                                className="text-center mt-8"
                            >
                                <button
                                    onClick={() => {
                                        resetForm();
                                        setTimeout(() => {
                                            calculatorSectionRef.current?.scrollIntoView(
                                                {
                                                    behavior: "smooth",
                                                    block: "start",
                                                }
                                            );
                                        }, 100);
                                    }}
                                    className="px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-full font-semibold hover:shadow-xl transition-all duration-300"
                                >
                                    Hitung Penilaian Lainnya
                                </button>
                            </motion.div>
                        </motion.div>
                    </div>
                </section>
            )}

            {/* Information Section */}
            <section className="py-20 relative overflow-hidden">
                {/* Glass backdrop */}
                <div className="absolute inset-0 backdrop-blur-sm bg-black/20" />
                <div className="container mx-auto px-6 relative z-10">
                    <motion.div
                        initial={{ opacity: 0, y: 50 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="max-w-4xl mx-auto relative"
                    >
                        <h2 className="text-4xl font-bold text-white text-center mb-12 relative z-10">
                            Tentang Kalkulator Ini
                        </h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.2 }}
                                className="bg-white/15 rounded-xl p-6 border border-white/30 shadow-xl relative"
                            >
                                <h3 className="text-xl font-bold text-white mb-4">
                                    📊 Standar WHO
                                </h3>
                                <p className="text-gray-300 leading-relaxed">
                                    Kalkulator ini menggunakan Standar
                                    Pertumbuhan Anak WHO yang memberikan
                                    deskripsi terbaik tentang pertumbuhan
                                    fisiologis untuk anak di bawah 5 tahun.
                                </p>
                            </motion.div>

                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.4 }}
                                className="bg-white/15 rounded-xl p-6 border border-white/30 shadow-xl relative"
                            >
                                <h3 className="text-xl font-bold text-white mb-4">
                                    📏 Skor Z Tinggi-menurut-Umur
                                </h3>
                                <p className="text-gray-300 leading-relaxed">
                                    Skor Z Tinggi-menurut-Umur (HAZ) menunjukkan
                                    berapa standar deviasi tinggi anak dari
                                    median tinggi anak-anak seusia dan sejenis
                                    kelamin.
                                </p>
                            </motion.div>

                            <motion.div
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.6 }}
                                className="bg-white/15 rounded-xl p-6 border border-white/30 shadow-xl relative"
                            >
                                <h3 className="text-xl font-bold text-white mb-4">
                                    ⚠️ Catatan Penting
                                </h3>
                                <p className="text-gray-300 leading-relaxed">
                                    Alat ini hanya untuk tujuan skrining. Selalu
                                    konsultasi dengan tenaga kesehatan
                                    profesional untuk nasihat medis dan
                                    diagnosis yang tepat.
                                </p>
                            </motion.div>

                            <motion.div
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.8, delay: 0.8 }}
                                className="bg-white/15 rounded-xl p-6 border border-white/30 shadow-xl relative"
                            >
                                <h3 className="text-xl font-bold text-white mb-4">
                                    🎂 Perhitungan Umur
                                </h3>
                                <p className="text-gray-300 leading-relaxed">
                                    Umur dihitung dalam bulan. Jika tanggal
                                    lahir setelah tanggal 15, akan dihitung
                                    sebagai bulan berikutnya untuk penilaian
                                    yang lebih akurat.
                                </p>
                            </motion.div>
                        </div>
                    </motion.div>
                </div>
            </section>
        </>
    );
};

export default StuntingCalculator;
