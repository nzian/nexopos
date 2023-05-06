/**
 * will bootstrap time
 */
import Vue from 'vue';
import './shared/time';
import * as baseComponents from './components/components';

import VirtualCollection from 'vue-virtual-collection';
import { NsHotPress } from './libraries/ns-hotpress';

const nsPos                     =   () => import( './pages/dashboard/pos/ns-pos.vue' );
const nsPosCart                 =   () => import( './pages/dashboard/pos/ns-pos-cart.vue' );
const nsPosGrid                 =   () => import( './pages/dashboard/pos/ns-pos-grid.vue' );
const nsProductGrid                 =   () => import( './popups/pos/ns-pos-quick-product-popup.vue' );

( window as any ).nsComponents  =   { ...baseComponents };
( window as any ).nsHotPress    =   new NsHotPress;

Vue.use( VirtualCollection );

new Vue({
    el: '#pos-app',
    mounted() {
        // ...
    },
    components: {
        nsPos,
        nsPosCart,
        nsProductGrid,
        ...( window as any ).nsComponents,
    }
})