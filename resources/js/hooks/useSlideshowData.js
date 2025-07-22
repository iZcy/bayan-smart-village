// resources/js/hooks/useSlideshowData.js
import { useMemo } from 'react';

/**
 * Custom hook to prepare slideshow data from various content types
 * @param {Array} data - Raw data array
 * @param {Object} config - Configuration object
 * @param {number} config.limit - Number of items to include (default: 5)
 * @param {Function} config.mapper - Custom mapper function
 * @param {Function} config.filter - Custom filter function
 * @param {string} config.type - Data type for default mapping ('places', 'products', 'articles', 'gallery')
 * @returns {Array} Formatted slideshow data
 */
export const useSlideshowData = (data = [], config = {}) => {
    const {
        limit = 5,
        mapper,
        filter,
        type = 'default'
    } = config;

    return useMemo(() => {
        if (!Array.isArray(data) || data.length === 0) {
            return [];
        }

        let processedData = data;

        // Apply custom filter if provided
        if (filter && typeof filter === 'function') {
            processedData = processedData.filter(filter);
        }

        // Limit the data
        processedData = processedData.slice(0, limit);

        // Apply custom mapper if provided
        if (mapper && typeof mapper === 'function') {
            return processedData.map(mapper);
        }

        // Default mappers based on type
        switch (type) {
            case 'places':
                return processedData.map((place) => ({
                    id: place.id,
                    image_url: place.image_url,
                    title: place.name,
                    subtitle: place.category?.name || "Discover this place",
                }));

            case 'products':
                return processedData
                    .filter((product) => product && product.name)
                    .map((product) => ({
                        id: product.id,
                        image_url: 
                            product.primary_image_url || 
                            product.image_url || 
                            product.images?.[0]?.image_url ||
                            null,
                        title: product.name,
                        subtitle: 
                            product.short_description ||
                            product.sme?.name ||
                            "Quality local product",
                    }));

            case 'articles':
                return processedData.map((article) => ({
                    id: article.id,
                    image_url: article.cover_image_url,
                    title: article.title,
                    subtitle: article.published_at
                        ? new Date(article.published_at).toLocaleDateString()
                        : "Village story",
                }));

            case 'smes':
                return processedData.map((sme) => ({
                    id: sme.id,
                    image_url: sme.logo_url,
                    title: sme.name,
                    subtitle: sme.type === 'product' ? 'Local Business' : 'Service Provider',
                }));

            case 'gallery':
                return processedData.map((image) => ({
                    id: image.id,
                    image_url: image.image_url,
                    title: image.caption || `Gallery image ${image.id}`,
                    subtitle: image.place?.name || "Village Gallery",
                }));

            default:
                // Generic mapper for unknown types
                return processedData.map((item) => ({
                    id: item.id,
                    image_url: item.image_url || item.cover_image_url || item.primary_image_url,
                    title: item.title || item.name || item.caption || `Item ${item.id}`,
                    subtitle: item.subtitle || item.description || item.category?.name || "",
                }));
        }
    }, [data, limit, mapper, filter, type]);
};

/**
 * Predefined configurations for common use cases
 */
export const slideshowConfigs = {
    places: {
        type: 'places',
        limit: 5,
        interval: 6000,
        transitionDuration: 1.5,
        placeholderConfig: {
            gradient: "from-green-600 to-teal-700",
            icon: "🏞️",
            text: "No places to showcase"
        }
    },
    
    products: {
        type: 'products',
        limit: 5,
        interval: 6000,
        transitionDuration: 1.5,
        filter: (product) => product && product.name,
        placeholderConfig: {
            gradient: "from-emerald-600 to-green-700",
            icon: "📦",
            text: "No products available"
        }
    },
    
    articles: {
        type: 'articles',
        limit: 5,
        interval: 6000,
        transitionDuration: 1.5,
        placeholderConfig: {
            gradient: "from-blue-600 to-purple-700",
            icon: "📖",
            text: "No stories to tell"
        }
    },
    
    smes: {
        type: 'smes',
        limit: 5,
        interval: 6000,
        transitionDuration: 1.5,
        placeholderConfig: {
            gradient: "from-orange-600 to-amber-700",
            icon: "🏢",
            text: "No businesses to showcase"
        }
    },
    
    gallery: {
        type: 'gallery',
        limit: 8,
        interval: 5000,
        transitionDuration: 2,
        placeholderConfig: {
            gradient: "from-purple-600 to-pink-700",
            icon: "📸",
            text: "No gallery images"
        }
    }
};

export default useSlideshowData;