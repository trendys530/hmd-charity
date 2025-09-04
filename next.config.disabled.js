/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'export',
  // Remove the basePath, images, and trailingSlash options
  reactStrictMode: true,
};

module.exports = nextConfig;
