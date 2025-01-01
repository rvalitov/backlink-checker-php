import eslintPluginYml from "eslint-plugin-yml";

export default [
  {
    ignores: ["node_modules/**/*", "vendor/**/*"],
  },
  ...eslintPluginYml.configs["flat/recommended"],
];
