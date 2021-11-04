<template>
  <v-card class="ma-2 pl-3 pr-3 pb-3">
    <v-card-title class="pa-1" >
      <span class="ml-5">{{ $t("dealers.dealers_centers") }}</span>
      <v-spacer></v-spacer>
      <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
          dense
      ></v-text-field>
      <v-btn v-if="$store.getters.checkPermission('ii001_file-create')" 
        class="ml-5 success" @click="createfile('II001')" :loading="loading1">
        II001 file 
      </v-btn>

      <v-btn v-if="$store.getters.checkPermission('ii002_file-create')" 
        class="ml-5 primary" @click="createfile('II002')" :loading="loading2">
        II002 file 
      </v-btn>

    </v-card-title>
    <v-data-table
        dense
        :headers="headers"
        :items="items"
        :search="search"
        item-key="name"
        class="elevation-5"
        fixed-header
        single-expand
        :options.sync="dataTableOptions"
        :server-items-length="server_items_length"
        :disable-pagination="true"
        :footer-props="{
           itemsPerPageOptions: [10, 50, 500],
          showFirstLastPage: true,
        }"
        @update:page="updatePage"
        @update:items-per-page="updatePerPage"
    >
      <template v-slot:item.created_at="{ item }">
        {{ datetimeFormat(item.created_at) }}
      </template>
    </v-data-table>
  </v-card>
</template>
<script>
import moment from "moment";
import Swal from "sweetalert2";
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    onOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });

export default {
  data() {
    return{
      dataTableOptions: {
        page: 1,
        itemsPerPage: 10
      },
      page: 1,
      loading: false,
      loading1: false,
      loading2: false,
      server_items_length: -1,
      items:[],
      search: '',
      Dealer: false,
      DealerTitle: "",
      form: {},
    };
  },
  computed:{
    headers() {
      return [
        {
          text:  this.$t("uploadvin.vin_gm"),
          // Original VIN, Orginal VIn
          value: "vin_gm",
        },
        {
          text:  this.$t("uploadvin.vin_local"),
          // New VIN, noviye VIN
          value: "vin_local"
        },
        {
          text:  this.$t("uploadvin.model_code"),
          //Model code, KOd model
          value: "model_code",
          width: 15
        },
        {
          text:  this.$t("uploadvin.model_year"),
          //Model Year, God model
          value: "model_year",
          width: 15
        },

        {
          text: this.$t("uploadvin.engine"),
          //Engine number, nomer dvigatel
          value: "engine"
        },
        {
          text: this.$t("uploadvin.full_option"),
          //Full Option, Optsiyi
          value: "full_option",
          // width: 15
        },
        {
          text: this.$t("uploadvin.produced_date"),
          //Produce date, data Vipuska
          value: "produced_date"
        },
        {
          text: this.$t("uploadvin.to_dealer"),
          // Dealer
          value: "to_dealer"
        },
        {
          text: this.$t("uploadvin.sold_date"),
          //Retail sales date,
          value: "sold_date"
        },
      ];
    },
  },
  methods: {
    createfile(filetype) {
      if (filetype == 'II001') {
        this.loading1 = true;
      }
      if (filetype == 'II002') {
        this.loading2 = true;
      }
      axios
          .get( this.$store.state.backend_url + "api/filecreate/" + filetype)
          .then((res) => {
            if (res.data.status == '0'){
              Toast.fire({
                icon: "warning",
                title: "Message: " + res.data.message,
              });              
            }

            if (res.data.status == '1'){
              Toast.fire({
                icon: "success",
                title: "Message: " + res.data.message,
              });              
            }
            this.loading1 = false;
            this.loading2 = false;

          })
          .catch((err) => {
            console.log(err.message);
            this.loading1 = false;
            this.loading2 = false;
          });      
    },
    updatePage($event) {
      this.getList();
    },
    updatePerPage($event) {
      this.getList();
    },
    datetimeFormat(date) {
      return moment(date).format("DD.MM.YYYY HH:mm:ss");
    },

    getList() {
      axios.post(this.$store.state.backend_url + "api/excel/vin",{
        pagination: this.dataTableOptions
      })
          .then((res)=>{
            this.server_items_length = res.data.total;
            this.items = res.data.data;
          })
          .catch((error)=>{
            console.log(error);
          })
    }
  },
  mounted() {
    this.getList();
  },
}
</script>
<style>
.text-start {
  border: 1px solid #96B4D8;
}
.text-right {
  border: 1px solid #96B4D8;
}
.text-center {
  border: 1px solid #96B4D8;
}

.v-data-table-header th {
  background-color: #CEE2F7 !important;
  color: black !important;
}
/*#table-header {*/
/*  background-color: #CEE2F7 !important;*/
/*  color: #337ab7 !important;*/
/*}*/


/*.v-data-table-header {*/
/*    color: white;*/
/*}*/
</style>