const path = require('path');

module.exports = {
  entry: {
    main: './src/index.js',
    admin: './src/admin.js',
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'static/js/[name].js',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        },
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
};
