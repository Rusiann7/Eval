const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    host: "0.0.0.0",
    https: true,
    allowedHosts: "all",
    client: {
      overlay: true,
      logging: 'info',
    },
    server: {
      type: 'https',
    },
    hot: true,
    watchFiles: {
      paths: ['src/**/*'],
      options: {
        usePolling: true,
        interval: 1000,
      },
    },
  },
});
