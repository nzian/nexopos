import Vue from 'vue';
const nsAutoCompleteInput      =   Vue.component( 'ns-auto-complete-input', {
    data: () => {
        return {
            searchVendorValue: '',
            debounceSearch: null,
            vendors: [],
            isLoading: false,
        }
    },
    mounted() {
        // ...
        this.getRecentVendors();
    },

    watch: {
        searchVendorValue( value ) {
            clearTimeout( this.debounceSearch );
            this.debounceSearch     =   setTimeout( () => {
                this.searchVendor( value );
            }, 500 );
        }
    },

    methods: {
        getRecentVendors() {
            this.isLoading  =   true;

            nsHttpClient.get( '/api/nexopos/v4/smp/vendor/recently-active' )
                .subscribe({
                    next: vendors => {
                        this.isLoading  =   false;
                        vendors.forEach( vendor => vendor.selected = false );
                        this.vendors  =   vendors;
                    },
                    error: ( error ) => {
                        this.isLoading  =   false;
                    }
                });
        },

        attemptToChoose() {
            if ( this.vendors.length === 1 ) {
                return this.selectVendor( this.vendors[0] );
            }
        },
        searchVendor( value ) {
            nsHttpClient.post( '/api/nexopos/v4/smp/vendor/search', {
                search: value
            }).subscribe( vandors => {
                vandors.forEach( vandor => vandor.selected = false );
                this.vandors  =   vandors;
            })
        },

        selectVendor( vendor ) {
            this.vendors.forEach( vendor => vendor.selected = false );
            vendor.selected   =   true;
            this.isLoading      =   false;
            this.searchVendorValue = vendor.vendor_id;
            this.field.value = vendor.vendor_id;
        },
    },

  
    computed: {
        vendorSelected() {
            return false;
        },
        hasError() {
            if ( this.field.errors !== undefined && this.field.errors.length > 0 ) {
                return true;
            }
            return false;
        },
        disabledClass() {
            return this.field.disabled ? 'ns-disabled cursor-not-allowed' : '';
        },
        inputClass() {
            return this.disabledClass + ' ' + this.leadClass;
        },
        leadClass() {
            return this.leading ? 'pl-8' : 'px-4';
        }
    },
    props: [ 'placeholder', 'leading', 'type', 'field' ],
    template: `
    <div class="flex flex-col mb-2 flex-auto ns-input" :class="field.hidden ? 'hidden' : ''">
        <label :for="field.name" :class="hasError ? 'has-error' : 'is-pristine'" class="block leading-5 font-medium"><slot></slot></label>
        <div :class="hasError ? 'has-error' : 'is-pristine'" class="mt-1 relative overflow-hidden border-2 rounded-md focus:shadow-sm mb-2">
            <div v-if="leading" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="leading sm:text-sm sm:leading-5">
                {{ leading }}
                </span>
            </div>
            <input 
                :disabled="field.disabled" 
                @blur="$emit( 'blur', this )" 
                @change="$emit( 'change', this )"
                @keydown.enter="attemptToChoose()"
                v-model="searchVendorValue"
                :id="field.name" :type="type || field.type || 'text'" 
                :class="inputClass" class="block w-full sm:text-sm sm:leading-5 h-10" :placeholder="placeholder" />
        </div>
        <p v-if="! field.errors || field.errors.length === 0" class="text-xs ns-description"><slot name="description"></slot></p>
        <p v-for="error of field.errors" class="text-xs ns-error">
            <slot v-if="error.identifier === 'required'" :name="error.identifier">This field is required.</slot>
            <slot v-if="error.identifier === 'email'" :name="error.identifier">This field must contain a valid email address.</slot>
            <slot v-if="error.identifier === 'invalid'" :name="error.identifier">{{ error.message }}</slot>
        </p>
    </div>
    `,
});

export { nsAutoCompleteInput }