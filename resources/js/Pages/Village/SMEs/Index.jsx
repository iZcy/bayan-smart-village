// resources/js/Pages/Village/SMEs/Index.jsx
import React, { useState } from "react";
import { Head, Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useInView } from "react-intersection-observer";
import MainLayout from "@/Layouts/MainLayout";
import MediaBackground from "@/Components/MediaBackground";
import { BaseCard } from "@/Components/Cards/Index";

export default function SMEsIndex({ village, smes }) {
    const [selectedType, setSelectedType] = useState('all');
    
    // Extract SMEs data from pagination object
    const smesData = smes?.data || smes || [];
    
    // Filter SMEs based on selected type
    const filteredSMEs = smesData.filter(sme => {
        if (selectedType === 'all') return true;
        return sme.type === selectedType;
    });

    // Content reveal
    const [contentRef, contentInView] = useInView({
        threshold: 0.3,
        triggerOnce: true,
    });

    return (
        <MainLayout title={`Businesses - ${village.name}`} description={`Discover local businesses and services in ${village.name}`}>
            <Head title={`Businesses - ${village.name}`} />

            {/* Media Background */}
            <MediaBackground
                context="smes"
                village={village}
                enableControls={true}
                blur={true}
                audioOnly={true}
                disableAudio={true}
                controlsId="smes-media-controls"
                fallbackVideo="/video/videobackground.mp4"
                fallbackAudio="/audio/sasakbacksong.mp3"
            />

            {/* Hero Section */}
            <section className="relative min-h-screen flex items-center justify-center">
                <div className="absolute inset-0 bg-gradient-to-br from-black/40 via-black/20 to-black/40" />
                
                <div className="relative z-10 text-center max-w-5xl mx-auto px-6 py-20">
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
                            <span className="text-white">Businesses</span>
                        </div>
                    </motion.nav>

                    {/* Title */}
                    <motion.h1
                        className="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-white via-blue-200 to-green-200 bg-clip-text text-transparent"
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 1, delay: 0.5 }}
                    >
                        Local Businesses
                    </motion.h1>

                    {/* Subtitle */}
                    <motion.p
                        className="text-lg md:text-xl text-white/90 max-w-3xl mx-auto leading-relaxed mb-12"
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.9 }}
                    >
                        Discover the vibrant business community in {village.name}. From traditional services to modern enterprises, explore what our local entrepreneurs have to offer.
                    </motion.p>

                    {/* Stats */}
                    <motion.div
                        className="flex flex-wrap items-center justify-center gap-4 mb-12"
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 1.1 }}
                    >
                        <span className="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-white font-semibold">
                            🏢 {smesData.length} {smesData.length === 1 ? 'Business' : 'Businesses'}
                        </span>
                        <span className="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-white font-semibold">
                            🏪 {smesData.filter(sme => sme.type === 'product').length} Product Businesses
                        </span>
                        <span className="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-white font-semibold">
                            🛠️ {smesData.filter(sme => sme.type === 'service').length} Service Businesses
                        </span>
                    </motion.div>

                    {/* Explore Button */}
                    <motion.button
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.8, delay: 1.3 }}
                        onClick={() => {
                            document
                                .getElementById("businesses")
                                .scrollIntoView({ behavior: "smooth" });
                        }}
                        className="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white rounded-full hover:bg-white/20 transition-all duration-300 border border-white/30"
                    >
                        Explore Businesses
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
                    </motion.button>
                </div>
            </section>

            {/* Main Content */}
            <div className="relative bg-white">
                {/* Businesses Section */}
                <section id="businesses" className="py-20">
                    <div className="container mx-auto px-6">
                        <motion.div
                            ref={contentRef}
                            initial={{ opacity: 0, y: 50 }}
                            animate={contentInView ? { opacity: 1, y: 0 } : {}}
                            transition={{ duration: 1 }}
                            className="max-w-7xl mx-auto"
                        >
                            {/* Filter Tabs */}
                            <div className="flex justify-center mb-12">
                                <div className="flex bg-gray-100 rounded-full p-1">
                                    {[
                                        { key: 'all', label: 'All Businesses', icon: '🏢' },
                                        { key: 'product', label: 'Products', icon: '🏪' },
                                        { key: 'service', label: 'Services', icon: '🛠️' }
                                    ].map((filter) => (
                                        <button
                                            key={filter.key}
                                            onClick={() => setSelectedType(filter.key)}
                                            className={`px-6 py-3 rounded-full font-medium transition-all duration-300 ${
                                                selectedType === filter.key
                                                    ? 'bg-blue-600 text-white shadow-lg'
                                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200'
                                            }`}
                                        >
                                            <span className="mr-2">{filter.icon}</span>
                                            {filter.label}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* SMEs Grid */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                                {filteredSMEs.map((sme, index) => (
                                    <motion.div
                                        key={sme.id}
                                        initial={{ opacity: 0, y: 50 }}
                                        animate={contentInView ? { opacity: 1, y: 0 } : {}}
                                        transition={{
                                            duration: 0.6,
                                            delay: index * 0.1,
                                        }}
                                        viewport={{ once: true }}
                                    >
                                        <Link href={`/smes/${sme.slug}`}>
                                            <BaseCard
                                                className="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200"
                                                hoverEffects={true}
                                            >
                                                <div className="relative h-48 overflow-hidden">
                                                    {sme.logo_url ? (
                                                        <img
                                                            src={sme.logo_url}
                                                            alt={sme.name}
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                                            <span className="text-4xl text-white">
                                                                {sme.type === 'product' ? '🏪' : '🛠️'}
                                                            </span>
                                                        </div>
                                                    )}
                                                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                                    
                                                    {/* Type Badge */}
                                                    <div className="absolute top-3 right-3">
                                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                            sme.type === 'product' 
                                                                ? 'bg-green-500 text-white' 
                                                                : 'bg-blue-500 text-white'
                                                        }`}>
                                                            {sme.type === 'product' ? 'Products' : 'Services'}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div className="p-6">
                                                    <h3 className="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-1">
                                                        {sme.name}
                                                    </h3>
                                                    
                                                    <p className="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed h-[68px] overflow-hidden">
                                                        {sme.description}
                                                    </p>
                                                    
                                                    <div className="flex items-center justify-between">
                                                        {sme.contact_phone && (
                                                            <div className="flex items-center text-sm text-gray-500">
                                                                <span className="mr-1">📞</span>
                                                                <span className="truncate">{sme.contact_phone}</span>
                                                            </div>
                                                        )}
                                                        
                                                        {sme.offers && sme.offers.length > 0 && (
                                                            <span className="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                                                {sme.offers.length} {sme.type === 'product' ? 'Products' : 'Services'}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </BaseCard>
                                        </Link>
                                    </motion.div>
                                ))}
                            </div>

                            {/* Empty State */}
                            {filteredSMEs.length === 0 && (
                                <motion.div
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    className="text-center py-16"
                                >
                                    <div className="text-6xl mb-4">🏢</div>
                                    <h3 className="text-2xl font-bold text-gray-900 mb-2">
                                        No {selectedType === 'all' ? '' : selectedType} businesses found
                                    </h3>
                                    <p className="text-gray-600">
                                        {selectedType === 'all' 
                                            ? `There are no businesses registered in ${village.name} yet.`
                                            : `There are no ${selectedType} businesses in ${village.name} yet.`
                                        }
                                    </p>
                                </motion.div>
                            )}
                        </motion.div>
                    </div>
                </section>
            </div>
        </MainLayout>
    );
}