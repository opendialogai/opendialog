/**
 * The Path module.
 */
const path = require('path');

/**
 * Custom Webpack config.
 *
 * Described here, in the separate file, for the IDE support.
 *
 * @see https://gist.github.com/nachodd/4e120492a5ddd56360e8cff9595753ae
 */
module.exports = {
    resolve: {
        alias: {
            /**
             * An alias for the JS imports.
             *
             * Example of usage:
             * require('@/components/ComponentName');
             */
            '@': path.resolve(__dirname, './resources/js'),

            /**
             * An alias for the SASS imports.
             *
             * Example of usage:
             * @import "@sass/_vars";
             */
            'sass': path.resolve(__dirname, './resources/sass'),
        }
    }
};
