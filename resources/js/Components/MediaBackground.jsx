// resources/js/Components/MediaBackground.jsx
import React, { useState, useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";

const MediaBackground = ({
    context = "home",
    village = null,
    className = "",
    enableControls = false,
    fallbackVideo = "/video/videobackground.mp4",
    fallbackAudio = "/audio/sasakbacksong.mp3",
    onMediaLoad = null,
    overlay = true,
    blur = false,
    controlsId = "global-media-controls", // Unique ID to prevent conflicts
    audioOnly = false, // New prop to disable video and only show audio controls
    disableAudio = false, // New prop to completely disable audio
}) => {
    const [backgroundVideo, setBackgroundVideo] = useState(null);
    const [backgroundAudio, setBackgroundAudio] = useState(null);
    const [isVideoLoaded, setIsVideoLoaded] = useState(false);
    const [isAudioLoaded, setIsAudioLoaded] = useState(false);
    const [isAudioPlaying, setIsAudioPlaying] = useState(false);
    const [videoError, setVideoError] = useState(false);
    const [audioError, setAudioError] = useState(false);
    const [userHasInteracted, setUserHasInteracted] = useState(false);
    const [isFading, setIsFading] = useState(false);

    const videoRef = useRef(null);
    const audioRef = useRef(null);
    const fadeIntervalRef = useRef(null);

    // Fetch village and context-specific media from API
    useEffect(() => {
        const fetchMedia = async () => {
            try {
                // Fetch background video (only if not audio-only mode)
                if (!audioOnly) {
                    const videoResponse = await fetch(
                        `/api/media/${context}/featured?type=video`
                    );
                    if (videoResponse.ok) {
                        const videoData = await videoResponse.json();
                        if (videoData.media) {
                            setBackgroundVideo(videoData.media);
                            console.log(
                                `Loaded ${context} video:`,
                                videoData.media.title
                            );
                        } else {
                            console.log(
                                `No featured video found for ${context} context in ${
                                    videoData.village || "global"
                                }`
                            );
                        }
                    } else {
                        console.log(
                            `Failed to fetch ${context} video:`,
                            videoResponse.status
                        );
                    }
                }

                // Skip API and use fallback audio directly (only if audio is not disabled)
                if (!disableAudio) {
                    console.log(`Using fallback audio: ${fallbackAudio}`);
                    // Use fallback audio with default settings
                    setBackgroundAudio({
                        file_url: fallbackAudio,
                        title: "Background Music",
                        autoplay: true,
                        loop: true,
                        volume: 30,
                        muted: false
                    });
                }
            } catch (error) {
                console.log(
                    "Failed to fetch village-specific media, using fallbacks:",
                    error
                );
            }
        };

        if (context) {
            fetchMedia();
        }
    }, [context, village?.id, audioOnly, disableAudio]);

    // Audio fade utility functions
    const fadeIn = (duration = 1000) => {
        if (!audioRef.current) return;
        
        const targetVolume = backgroundAudio?.volume || 0.3;
        const steps = 50;
        const stepTime = duration / steps;
        const volumeStep = targetVolume / steps;
        
        audioRef.current.volume = 0;
        setIsFading(true);
        
        let currentStep = 0;
        fadeIntervalRef.current = setInterval(() => {
            currentStep++;
            if (currentStep <= steps && audioRef.current) {
                audioRef.current.volume = Math.min(volumeStep * currentStep, targetVolume);
                
                if (currentStep >= steps) {
                    clearInterval(fadeIntervalRef.current);
                    setIsFading(false);
                }
            } else {
                clearInterval(fadeIntervalRef.current);
                setIsFading(false);
            }
        }, stepTime);
    };

    const fadeOut = (duration = 1000, callback = null) => {
        if (!audioRef.current) return;
        
        const initialVolume = audioRef.current.volume;
        const steps = 50;
        const stepTime = duration / steps;
        const volumeStep = initialVolume / steps;
        
        setIsFading(true);
        
        let currentStep = 0;
        fadeIntervalRef.current = setInterval(() => {
            currentStep++;
            if (currentStep <= steps && audioRef.current) {
                audioRef.current.volume = Math.max(initialVolume - (volumeStep * currentStep), 0);
                
                if (currentStep >= steps) {
                    clearInterval(fadeIntervalRef.current);
                    setIsFading(false);
                    if (callback) callback();
                }
            } else {
                clearInterval(fadeIntervalRef.current);
                setIsFading(false);
                if (callback) callback();
            }
        }, stepTime);
    };

    // Clear fade interval on unmount
    useEffect(() => {
        return () => {
            if (fadeIntervalRef.current) {
                clearInterval(fadeIntervalRef.current);
            }
        };
    }, []);

    // Handle video load
    const handleVideoLoad = () => {
        setIsVideoLoaded(true);
        setVideoError(false);
        if (onMediaLoad) {
            onMediaLoad({ type: "video", loaded: true });
        }
    };

    // Handle video error
    const handleVideoError = () => {
        setVideoError(true);
        console.log("Video failed to load, using fallback");
        if (onMediaLoad) {
            onMediaLoad({ type: "video", loaded: false, error: true });
        }
    };

    // Handle audio load
    const handleAudioLoad = () => {
        setIsAudioLoaded(true);
        setAudioError(false);
        if (audioRef.current) {
            // Start with volume at 0 for fade in effect
            audioRef.current.volume = 0;
            // Always try to play immediately, regardless of autoplay setting
            audioRef.current
                .play()
                .then(() => {
                    console.log(
                        "Audio autoplay successful:",
                        backgroundAudio?.title || "fallback audio"
                    );
                    setIsAudioPlaying(true);
                    // Fade in the audio
                    fadeIn(2000); // 2 second fade in
                })
                .catch((error) => {
                    console.log(
                        "Audio autoplay blocked by browser policy:",
                        error.message
                    );
                    console.log("Audio will be available for manual play");
                    setIsAudioPlaying(false);
                });
        }
        if (onMediaLoad) {
            onMediaLoad({ type: "audio", loaded: true });
        }
    };

    // Handle audio error
    const handleAudioError = () => {
        setAudioError(true);
        console.log("Audio failed to load, using fallback");
        if (onMediaLoad) {
            onMediaLoad({ type: "audio", loaded: false, error: true });
        }
    };

    // Handle first user interaction to enable autoplay
    const handleFirstInteraction = () => {
        if (
            !userHasInteracted &&
            audioRef.current &&
            !isAudioPlaying
        ) {
            setUserHasInteracted(true);
            // Start with volume at 0 for fade in effect
            audioRef.current.volume = 0;
            audioRef.current
                .play()
                .then(() => {
                    console.log(
                        "Audio started after user interaction:",
                        backgroundAudio?.title || "fallback audio"
                    );
                    setIsAudioPlaying(true);
                    // Fade in the audio
                    fadeIn(2000); // 2 second fade in
                })
                .catch((error) => {
                    console.log(
                        "Audio failed even after user interaction:",
                        error.message
                    );
                });
        }
    };

    // Add global click listener to enable autoplay after user interaction
    useEffect(() => {
        if (isAudioLoaded && !userHasInteracted && !isAudioPlaying) {
            const enableAutoplayOnInteraction = () => {
                handleFirstInteraction();
                // Remove listener after first interaction
                document.removeEventListener(
                    "click",
                    enableAutoplayOnInteraction
                );
            };

            document.addEventListener("click", enableAutoplayOnInteraction);

            return () => {
                document.removeEventListener(
                    "click",
                    enableAutoplayOnInteraction
                );
            };
        }
    }, [isAudioLoaded, userHasInteracted, isAudioPlaying]);

    // Toggle audio playback with fade effects
    const toggleAudio = () => {
        if (audioRef.current) {
            if (isAudioPlaying) {
                // Fade out before pausing
                fadeOut(1000, () => {
                    if (audioRef.current) {
                        audioRef.current.pause();
                        setIsAudioPlaying(false);
                    }
                });
            } else {
                // Start with volume at 0 and fade in
                audioRef.current.volume = 0;
                audioRef.current.play().then(() => {
                    setIsAudioPlaying(true);
                    fadeIn(1000); // 1 second fade in for manual toggle
                }).catch(console.log);
            }
        }
    };

    // Get video source
    const getVideoSource = () => {
        if (backgroundVideo && !videoError) {
            return backgroundVideo.file_url;
        }
        return fallbackVideo;
    };

    // Get audio source
    const getAudioSource = () => {
        if (backgroundAudio && !audioError) {
            return backgroundAudio.file_url;
        }
        return fallbackAudio;
    };

    // Get video props
    const getVideoProps = () => {
        const defaultProps = {
            autoPlay: true,
            muted: true,
            loop: true,
            playsInline: true,
        };

        if (backgroundVideo) {
            return {
                ...defaultProps,
                autoPlay: backgroundVideo.autoplay,
                muted: backgroundVideo.muted,
                loop: backgroundVideo.loop,
            };
        }

        return defaultProps;
    };

    // Get audio props
    const getAudioProps = () => {
        const defaultProps = {
            loop: true,
            muted: false, // Don't mute by default
        };

        if (backgroundAudio) {
            return {
                ...defaultProps,
                loop: backgroundAudio.loop,
                muted: backgroundAudio.muted, // Use backend muted setting
            };
        }

        return defaultProps;
    };

    return (
        <div className={`${audioOnly ? "" : "fixed inset-0 z-0"} ${className}`}>
            {/* Background Video - Only render if not audio-only mode */}
            {!audioOnly && (
                <video
                    ref={videoRef}
                    className="w-full h-full object-cover pointer-events-none"
                    onLoadedData={handleVideoLoad}
                    onError={handleVideoError}
                    {...getVideoProps()}
                >
                    <source src={getVideoSource()} type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
            )}

            {/* Background Audio - Only render if not disabled */}
            {!disableAudio && (
                <audio
                    ref={audioRef}
                    onLoadedData={handleAudioLoad}
                    onError={handleAudioError}
                    {...getAudioProps()}
                >
                    <source src={getAudioSource()} type="audio/mpeg" />
                    Your browser does not support the audio tag.
                </audio>
            )}

            {/* Overlay */}
            {overlay && <div className="absolute inset-0 bg-black/20" />}

            {/* Blur overlay for index pages */}
            {blur && <div className="absolute inset-0 backdrop-blur-sm" />}

            {/* Loading indicator - Only show for video when not audio-only mode */}
            <AnimatePresence>
                {!audioOnly && !isVideoLoaded && !videoError && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="absolute inset-0 bg-gradient-to-br from-blue-900 to-purple-900 flex items-center justify-center"
                    >
                        <div className="text-white text-center">
                            <motion.div
                                animate={{ rotate: 360 }}
                                transition={{
                                    duration: 2,
                                    repeat: Infinity,
                                    ease: "linear",
                                }}
                                className="w-12 h-12 border-4 border-white/30 border-t-white rounded-full mx-auto mb-4"
                            />
                            <p>Loading media...</p>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* Media Controls */}
            {enableControls &&
                ((!audioOnly && isVideoLoaded) || isAudioLoaded) && (
                    <div
                        id={controlsId}
                        className="fixed top-24 right-8 z-[99999] flex gap-2"
                    >
                        {/* Audio Control - Only show if not disabled */}
                        {!disableAudio && isAudioLoaded && (
                            <motion.button
                                onClick={toggleAudio}
                                disabled={isFading}
                                className={`bg-black/30 backdrop-blur-md text-white p-3 rounded-full hover:bg-black/50 transition-colors relative ${
                                    !userHasInteracted && !isAudioPlaying
                                        ? "ring-2 ring-yellow-400 ring-opacity-75 animate-pulse"
                                        : ""
                                } ${isFading ? "opacity-50 cursor-not-allowed" : ""}`}
                                whileHover={{ scale: isFading ? 1 : 1.1 }}
                                whileTap={{ scale: isFading ? 1 : 0.9 }}
                                title={
                                    isFading
                                        ? "Audio transitioning..."
                                        : !userHasInteracted && !isAudioPlaying
                                        ? "Click to enable audio"
                                        : isAudioPlaying
                                        ? "Fade out audio"
                                        : "Fade in audio"
                                }
                            >
                                <span className="text-lg">
                                    {isFading ? "🎵" : isAudioPlaying ? "🔊" : "🔇"}
                                </span>
                                {/* Notification dot for pending autoplay */}
                                {!userHasInteracted && !isAudioPlaying && !isFading && (
                                    <motion.span
                                        animate={{ scale: [1, 1.2, 1] }}
                                        transition={{
                                            duration: 1,
                                            repeat: Infinity,
                                        }}
                                        className="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full"
                                    />
                                )}
                                {/* Fade indicator */}
                                {isFading && (
                                    <motion.span
                                        animate={{ rotate: 360 }}
                                        transition={{
                                            duration: 1,
                                            repeat: Infinity,
                                            ease: "linear",
                                        }}
                                        className="absolute -top-1 -right-1 w-3 h-3 bg-blue-400 rounded-full"
                                    />
                                )}
                            </motion.button>
                        )}

                    </div>
                )}

            {/* Debug Info (only in development) */}
            {process.env.NODE_ENV === "development" && !disableAudio && isAudioLoaded && (
                <div className="fixed bottom-4 left-4 z-50 bg-black/80 text-white px-3 py-2 rounded text-xs max-w-sm">
                    <div>Audio: {backgroundAudio?.title || "Fallback"}</div>
                    <div>Status: {isFading ? "Fading..." : isAudioPlaying ? "Playing" : "Stopped"}</div>
                    <div>
                        Autoplay: Always ON
                    </div>
                    <div>Muted: {backgroundAudio?.muted ? "YES" : "NO"}</div>
                    <div>Volume: {backgroundAudio?.volume || "default"}</div>
                    <div>
                        User Interaction: {userHasInteracted ? "YES" : "NO"}
                    </div>
                </div>
            )}

            {/* Error indicator */}
            {videoError && audioError && (
                <div className="absolute bottom-4 right-4 z-50 bg-red-500/80 text-white px-3 py-1 rounded text-sm">
                    Media loading failed, using defaults
                </div>
            )}
        </div>
    );
};

export default MediaBackground;
