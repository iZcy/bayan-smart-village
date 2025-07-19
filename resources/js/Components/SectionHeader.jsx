// resources/js/Components/SectionHeader.jsx
import React from "react";
import { motion } from "framer-motion";

const SectionHeader = ({
    title,
    subtitle,
    count,
    countLabel = "items",
    gradientColor = "from-blue-400 to-purple-400",
    textAlign = "center",
    className = "mb-16",
}) => {
    const alignmentClasses = {
        center: "text-center",
        left: "text-left",
        right: "text-right",
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className={`${alignmentClasses[textAlign]} ${className}`}
        >
            <h2 className="text-4xl font-bold text-white mb-4">
                {count !== undefined ? (
                    <>
                        {count} {title}
                        {count !== 1 && !title.endsWith("s") && "s"} Found
                    </>
                ) : (
                    title
                )}
            </h2>

            {subtitle && (
                <motion.p
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    transition={{ delay: 0.3, duration: 0.8 }}
                    className="text-xl text-white/80 mb-6"
                >
                    {subtitle}
                </motion.p>
            )}

            <motion.div
                initial={{ width: 0 }}
                whileInView={{
                    width: textAlign === "center" ? "10rem" : "8rem",
                }}
                transition={{ delay: 0.5, duration: 1 }}
                className={`h-1 bg-gradient-to-r ${gradientColor} ${
                    textAlign === "center" ? "mx-auto" : ""
                }`}
            />
        </motion.div>
    );
};

export default SectionHeader;
