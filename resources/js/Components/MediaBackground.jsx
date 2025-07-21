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
}) => {
    const [backgroundVideo, setBackgroundVideo] = useState(null);
    const [backgroundAudio, setBackgroundAudio] = useState(null);
    const [isVideoLoaded, setIsVideoLoaded] = useState(false);
    const [isAudioLoaded, setIsAudioLoaded] = useState(false);
    const [isAudioPlaying, setIsAudioPlaying] = useState(false);
    const [videoError, setVideoError] = useState(false);
    const [audioError, setAudioError] = useState(false);

    const videoRef = useRef(null);
    const audioRef = useRef(null);

    // Fetch media data from API
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
                        }
                    }
                }

                // Fetch background audio
                const audioResponse = await fetch(
                    `/api/media/${context}/featured?type=audio`
                );
                if (audioResponse.ok) {
                    const audioData = await audioResponse.json();
                    if (audioData.media) {
                        setBackgroundAudio(audioData.media);
                    }
                }
            } catch (error) {
                console.log("Failed to fetch media, using fallbacks:", error);
            }
        };

        fetchMedia();
    }, [context, village, audioOnly]);

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
        if (audioRef.current && backgroundAudio?.autoplay) {
            audioRef.current.volume = backgroundAudio.volume || 0.3;
            audioRef.current.play().catch(console.log);
            setIsAudioPlaying(true);
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

    // Toggle audio playback
    const toggleAudio = () => {
        if (audioRef.current) {
            if (isAudioPlaying) {
                audioRef.current.pause();
                setIsAudioPlaying(false);
            } else {
                audioRef.current.play().catch(console.log);
                setIsAudioPlaying(true);
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
        };

        if (backgroundAudio) {
            return {
                ...defaultProps,
                loop: backgroundAudio.loop,
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
                    className="w-full h-full object-cover"
                    onLoadedData={handleVideoLoad}
                    onError={handleVideoError}
                    {...getVideoProps()}
                >
                    <source src={getVideoSource()} type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
            )}

            {/* Background Audio */}
            <audio
                ref={audioRef}
                onLoadedData={handleAudioLoad}
                onError={handleAudioError}
                {...getAudioProps()}
            >
                <source src={getAudioSource()} type="audio/mpeg" />
                Your browser does not support the audio tag.
            </audio>

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
                        className="fixed top-24 right-8 z-50 flex gap-2"
                    >
                        {/* Audio Control */}
                        {isAudioLoaded && (
                            <motion.button
                                onClick={toggleAudio}
                                className="bg-black/30 backdrop-blur-md text-white p-3 rounded-full hover:bg-black/50 transition-colors"
                                whileHover={{ scale: 1.1 }}
                                whileTap={{ scale: 0.9 }}
                                title={
                                    isAudioPlaying ? "Mute Audio" : "Play Audio"
                                }
                            >
                                <span className="text-lg">
                                    {isAudioPlaying ? "🔊" : "🔇"}
                                </span>
                            </motion.button>
                        )}

                        {/* Media Info */}
                        {(backgroundVideo || backgroundAudio) && (
                            <motion.div
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                className="bg-black/30 backdrop-blur-md text-white px-4 py-2 rounded-full text-sm"
                            >
                                {backgroundVideo?.title ||
                                    backgroundAudio?.title ||
                                    "Default Media"}
                            </motion.div>
                        )}
                    </div>
                )}

            {/* Error indicator */}
            {videoError && audioError && (
                <div className="absolute bottom-4 left-4 z-50 bg-red-500/80 text-white px-3 py-1 rounded text-sm">
                    Media loading failed, using defaults
                </div>
            )}
        </div>
    );
};

export default MediaBackground;
