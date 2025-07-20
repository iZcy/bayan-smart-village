// resources/js/Components/Cards/BaseCard.jsx
import { motion } from "framer-motion";
import { Link } from "@inertiajs/react";
import { useInView } from "react-intersection-observer";

const BaseCard = ({
    children,
    href,
    index = 0,
    className = "",
    hoverEffects = true,
    ...props
}) => {
    const [ref, inView] = useInView({
        threshold: 0.1,
        triggerOnce: true,
    });

    const cardContent = (
        <motion.div
            ref={ref}
            initial={{ opacity: 0, y: 50 }}
            animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ duration: 0.6, delay: index * 0.1 }}
            whileHover={hoverEffects ? { y: -10, scale: 1.02 } : {}}
            className={`group bg-white/5 backdrop-blur-md rounded-2xl overflow-hidden border border-white/10 hover:border-white/30 transition-all duration-300 ${className}`}
            {...props}
        >
            {children}
        </motion.div>
    );

    return href ? <Link href={href}>{cardContent}</Link> : cardContent;
};

export default BaseCard;
