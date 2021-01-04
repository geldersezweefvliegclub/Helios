module.exports = {
  devServer: {
    disableHostCheck: true,
    proxy: 'http://helios.local/',
  },

  publicPath: process.env.NODE_ENV === 'production'
    ? '/install/'
    : '/',

  "transpileDependencies": [
    "vuetify"
  ]
}