import defaultTheme from "tailwindcss/defaultTheme";
import prose from "@tailwindcss/typography";

module.exports = {
  content: require("fast-glob").sync(
    [
      "source/**/*.{blade.php,blade.md,md,html,vue}",
      "!source/**/_tmp/*", // exclude temporary files
    ],
    { dot: true },
  ),
  theme: {
    extend: {
      fontFamily: {
        sans: ["Jost", ...defaultTheme.fontFamily.sans],
        heading: ["OpenSans", ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [prose],
};
