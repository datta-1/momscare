/** @type {import('next').NextConfig} */
const nextConfig = {
  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    // Warning: This allows production builds to successfully complete even if
    // your project has TypeScript errors.
    ignoreBuildErrors: true,
  },
  images: { 
    domains: ['images.unsplash.com', 'via.placeholder.com'],
    unoptimized: false 
  },
  webpack: (config) => {
    config.module.rules.push({
      test: /pdf\.worker\.(min\.)?mjs$/,
      type: "asset/resource",
    });
    return config;
  },
};

module.exports = nextConfig;
