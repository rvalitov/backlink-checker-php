/**
 * @see https://prettier.io/docs/en/configuration.html
 * @type {import("prettier").Config}
 */
const config = {
  overrides: [
    {
      files: "*.xml",
      options: {
        parser: "xml",
        printWidth: 120,
        plugins: ["@prettier/plugin-xml"]
      }
    }
  ]
};

module.exports = config;


