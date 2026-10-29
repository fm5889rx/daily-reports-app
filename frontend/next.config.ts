import type { NextConfig } from "next";

const backendUrl = (process.env.LARAVEL_APP_URL ?? "").replace(/\/+$/, "");

const nextConfig: NextConfig = {
  output: "standalone",
  async rewrites() {
    if (!backendUrl) {
      if (process.env.NODE_ENV === "development") {
        return [
          { source: "/api/:path*", destination: "http://127.0.0.1/api/:path*" },
          { source: "/auth/:path*", destination: "http://127.0.0.1/auth/:path*" },
          {
            source: "/sanctum/:path*",
            destination: "http://127.0.0.1/sanctum/:path*",
          },
        ];
      }

      if (process.env.VERCEL === "1") {
        throw new Error("LARAVEL_APP_URL must be set in the Vercel project.");
      }

      return [];
    }

    return [
      { source: "/api/:path*", destination: `${backendUrl}/api/:path*` },
      { source: "/auth/:path*", destination: `${backendUrl}/auth/:path*` },
      {
        source: "/sanctum/:path*",
        destination: `${backendUrl}/sanctum/:path*`,
      },
    ];
  },
};

export default nextConfig;
