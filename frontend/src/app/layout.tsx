import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Daily Log | 日報管理",
  description: "チームの日報を、見やすくシンプルに管理します。",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body>{children}</body>
    </html>
  );
}
