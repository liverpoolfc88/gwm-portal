<template>
  <v-card class="ma-2 pa-3">
    <v-card-title>
      <span class="ml-5">GWM II002 files</span>
      <v-spacer></v-spacer>
      <v-btn v-if="$store.getters.checkPermission('ii002_file-send')" 
          class="ml-5 success" @click="filesend('II002')" :loading="loading1">
          send II002 files
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
    <template v-slot:item.status="{ item }">
      {{ item.status == '1' ? 'Sent' : 'Not sent yet' }}
    </template>
    </v-data-table>

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
      items:[],
      server_items_length: -1,
      search: '',
    }
  },
  computed:{
    headers() {
      return [
        {
          text: "FileName",
          value: "filename"
        },
        {
          text: "Record count",
          value: "recordcount"
        },
        {
          text: "Status",
          value: "status"
        },
        {
          text: "created_at",
          value: "created_at"
        },
      ];
    },
  },

  methods: {
    datetimeFormat(date) {
      return moment(date).format("DD.MM.YYYY HH:mm:ss");
    },
    filesend(filetype) {
      this.loading1 = true;
      axios
          .get( this.$store.state.backend_url + "api/filesend/" + filetype)
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
            this.getList();
          })
          .catch((err) => {
            console.log(err.message);
            this.loading1 = false;
          });      
    },
    updatePage($event) {
      this.getList();
    },
    updatePerPage($event) {
      this.getList();
    },

    getList() {
      this.loading = true;
      this.$axios.post(this.$store.state.backend_url + "api/ii002files" ,{
        pagination: this.dataTableOptions
      //   headers: {
      //     Authorization: JSON.parse(window.localStorage.getItem('access_token'))
      //   }
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

</style>