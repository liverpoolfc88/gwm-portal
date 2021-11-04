<template>
  <v-card class="ma-2 pl-3 pr-3 pb-3" :disabled="loading">
    <v-card-title class="pa-1">
      <span class="ml-5">{{ $t("realizedvin.sent_data") }}</span>
      <v-spacer></v-spacer>
      <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          :placeholder="$t('search')"
          single-line
          hide-details
          dense
      ></v-text-field>
      <v-btn v-if="$store.getters.checkPermission('sendvin-create')"
           @click="newVin()" color="success" class="ml-8 pl-1 pr-5" dark small >
        <v-icon class="mr-3" text>mdi-plus-box</v-icon>
        {{ $t("new") }}
      </v-btn>
    </v-card-title>
    <v-data-table
        dense
        fixed-header
        :loading="loading"
        :loading-text="$t('loadingText')"
        :no-data-text="$t('noDataText')"
        :headers="headers"
        :items="items"
        :options.sync="dataTableOptions"
        :search="search"
        item-key="id"
        :server-items-length="server_items_length"
        class="elevation-5"
        :disable-pagination="true"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 200],
          showFirstLastPage: true,
        }"
        @update:page="updatePage"
        @update:items-per-page="updatePerPage"
    >
        <template v-slot:item.created_at="{ item }">
          {{ datetimeFormat(item.created_at) }}
        </template>
        <template v-slot:item.sdate="{ item }">
          {{ dateFormat(item.sdate) }}
        </template>

    </v-data-table>

    <v-dialog v-model="loading" width="300" hide-overlay>
      <v-card color="primary" dark>
        <v-card-text>
          {{ $t("loadingText") }}
          <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="newVinno" persistent max-width="450px" @keydown.esc="newVinno = false">
      <v-card>
        <v-card-title>
          <span class="headline">{{ $t("realizedvin.add_vin") }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="newVinno = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-form ref="formVin">
            <v-container>
              <v-row>
                <v-col class="pt-0" cols="12">
                  <label>{{ 'VIN' }}</label>
                  <v-text-field :maxlength="max" v-model="form.vin" dense :rules="vinRules"></v-text-field>
                </v-col>
                <v-menu
                    v-model="menu2"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    transition="scale-transition"
                    offset-y
                    min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                        v-model="form.sdate"
                        :placeholder="$t('realizedvin.sdate')"
                        prepend-icon="mdi-calendar"
                        readonly
                        :rules="dateRules"
                        v-bind="attrs"
                        v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                      v-model="form.sdate"
                      @input="menu2 = false"
                  ></v-date-picker>
                </v-menu>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>

        <v-card-actions class="pt-0" >
          <v-spacer></v-spacer>
          <v-btn  color="green" dark @click="save" :loading="loading">{{ $t("save") }}</v-btn>
          <!--                        <v-btn color="red darken-1" dark @click="onClickOutside">{{ $t('close') }}</v-btn>-->
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
<script>
import moment from 'moment';
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

//const axios = require("axios").default;
export default {
  data() {
    return{
      dataTableOptions: {
        page: 1,
        itemsPerPage: 10
      },
      page: 1,
      loading: false,
      items:[],
      server_items_length: -1,
      max: 17,
      date: moment(new Date()).format("YYYY-MM-DD"),
      search: '',
      newVinno: false,
      VinTitle: "",
      form: {},
      menu2: false,
      dateRules: [
        v => !!v || this.$t("realizedvin.date_required"),
        v => v <= this.date || this.$t("realizedvin.included"),
      ],
      vinRules: [
        v => !!v || this.$t("realizedvin.vin_required"),
        v => /^[a-zA-Z0-9]+$/.test(v) || this.$t("realizedvin.error_written"),
        v => v.length == 17 || this.$t("realizedvin.characters"),
      ],
    }
  },

  computed:{
    headers() {
      return [
        // {text:"#", value:'id', align:"center"},
        {
          text: this.$t("realizedvin.vin"),
          value: "vin"
        },
        {
          text: this.$t("realizedvin.sdate"),
          value: "sdate"
        },
        {
          text: this.$t("realizedvin.dealervin_name"),
          value: "dealervin.name"
        },
        {
          text: this.$t("created_at"),
          value: "created_at"
          // value: {{ dateFormat("created_at") }}
        },
        // {
        //   text: "",
        //   align: "right",
        //   value: "pencil",
        //   sortable: false,
        //   width: 40,
        // },
        // {
        //   text: "",
        //   align: "right",
        //   value: "delete",
        //   sortable: false,
        //   width: 40,
        // },
      ];
    },
  },
  methods: {
    updatePage($event) {
      this.getList();
    },
    updatePerPage($event) {
      this.getList();
    },
    datetimeFormat(date) {
      return moment(date).format("DD.MM.YYYY HH:mm:ss");
    },
    dateFormat(date) {
      return moment(date).format("DD.MM.YYYY");
    },
    newVin() {
      this.newVinno = true;
      this.VinTitle = "ADD Vin";
      this.form = {
        id: Date.now(),
        vin: "",
        sdate: "",
      };
    },
    editVin(item) {
      this.newVinno = true;
      this.VinTitle = this.$t("realizedvin.update"),
      this.form = JSON.parse(JSON.stringify(item));
    },

    deleteVin(id) {
      Swal.fire({
        title: this.$t("swal_sure"),
        text: this.$t("swal_delete"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: this.$t("delete"),
        cancelButtonText: this.$t("cancel"),
      })
          .then((result) => {
            if (result.value) {
              axios.delete(
                  this.$store.state.backend_url + "api/realizevin/delete/" + id
              )
              .then(res => {
                this.items = this.items.filter(v => v.id != id);
              })
              Swal.fire({
                position: "top-end",
                toast: true,
                icon: "success",
                title: "RealizedVin Succesfully deleted",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
              });
            }
          })
          .catch((error) => {
            //console.error(error);
            Swal.fire({
              position: "center",
              icon: "error",
              width: "250px",
              title: "swal_error_text",
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
            });
          });
    },
    save() {
      if (this.$refs.formVin.validate()){
        this.loading = true;
        axios
            .post(this.$store.state.backend_url + "api/realizevin/create", this.form)
            .then((res) => {
              
              if (res.data['status'] == 1) {
                this.getList();
                this.newVinno = false;

                Toast.fire({
                  icon: "success",
                  title: "RealizedVin successfully created",
                });
                this.loading = false;
              }else{
                    Toast.fire({
                        icon: "warning",
                        title: "SAP Service message: "+res.data['message']
                      });
                      this.loading = false;
                    }
            })
            .catch( (e) => {
              this.loading = false;
              Toast.fire({
                icon: "warning",
                title: "Error: " + Object.values(e.response.data.errors)
              });
            })
      }
    },
    getList() {
      this.loading = true;
      this.$axios.post(this.$store.state.backend_url + "api/realizevin" ,{
        pagination: this.dataTableOptions
      })
          .then((res) => {
            this.server_items_length = res.data.total;
            this.items = res.data.data;
            this.loading = false;
          })
          .catch((error) => {
            console.log(error);
          })
    },
  },
  mounted() {
    this.getList();
  }
}
</script>
<style>
.text-start {
  border: 1px solid #96B4D8;
}
.text-right {
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