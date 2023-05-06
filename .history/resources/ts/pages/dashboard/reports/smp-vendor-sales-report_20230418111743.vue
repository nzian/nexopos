<template>
    
</template>
<script>
import moment from "moment";
import nsDatepicker from "@/components/ns-datepicker";
import { default as nsDateTimePicker } from '@/components/ns-date-time-picker';
import { nsHttpClient, nsSnackBar } from '@/bootstrap';
import { __ } from '@/libraries/lang';

export default {
    name: 'smp-vendor-sales-report',
    data() {
        return {
            startDate: moment().subtract(30, 'days').calendar(),
            endDate: moment(),
            result: [],
            summary: {},
            field: {
                type: 'datetimepicker',
                value: '2021-02-07',
                name: 'date'
            }
        }
    },
    components: {
        nsDatepicker,
        nsDateTimePicker,
    },
    computed: {
        // ..
    },
    methods: {
        printVendorSaleReport() {
            this.$htmlToPaper( 'sale-report' );
        },
        setStartDate( moment ) {
            this.startDate  =   moment.format();
        },
        loadVendorReport() {
            if ( this.startDate === null || this.endDate ===null ) {
                return nsSnackBar.error( __( 'Unable to proceed. Select a correct time range.' ) ).subscribe();
            }

            const startMoment   =   moment( this.startDate );
            const endMoment     =   moment( this.endDate );

            if ( endMoment.isBefore( startMoment ) ) {
                return nsSnackBar.error( __( 'Unable to proceed. The current time range is not valid.' ) ).subscribe();
            }

            nsHttpClient.post( '/api/nexopos/v4/reports/vendor-report', { 
                startDate: this.startDate,
                endDate: this.endDate
            }).subscribe({
                next: response => {
                    this.result     =   response.result;
                    this.summary    =   response.summary;
                }, 
                error : ( error ) => {
                    nsSnackBar.error( error.message ).subscribe();
                }
            });
        },

        computeTotal( collection, attribute ) {
            if ( collection.length > 0 ) {
                return collection.map( entry => parseFloat( entry[ attribute ] ) )
                    .reduce( ( b, a ) => b + a );
            }

            return 0;
        },

        setEndDate( moment ) {
            this.endDate    =   moment.format();
        },
    }
}
</script>