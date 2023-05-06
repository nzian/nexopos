let mix     =   require( 'laravel-mix' );
require('mix-tailwindcss');
const path  =   require( 'path' );

mix.webpackConfig({
    resolve: {
        extensions: [ "*", ".js", ".jsx", ".vue", ".ts", ".tsx"],
        alias: {
            '@': path.resolve( __dirname, 'resources/ts/')
        }
    }
})
mix.disableNotifications();
mix.vue();
mix.sass("Resources/css/gastro.scss", "css" )
    .tailwind();
mix.ts( 'Resources/ts/Gastro', 'js' )
mix.ts( 'Resources/ts/GastroKitchen', 'js' )
    .setPublicPath( 'Public' )