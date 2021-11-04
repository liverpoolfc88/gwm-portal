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
      <v-btn v-if="$store.getters.checkPermission('dealer-create')" @click="newDealer()" color="success" class="ml-8 pl-1 pr-5" dark small >
        <v-icon class="mr-3" text>mdi-plus-box</v-icon>
        {{ $t("new") }}
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
           itemsPerPageOptions: [10, 50, 100],
          showFirstLastPage: true,
        }"
        @update:page="updatePage"
        @update:items-per-page="updatePerPage"
    >
      <template
          v-slot:item.id="{ item }"
      >{{items.map(function(x) {return x.id; }).indexOf(item.id) + 1}}</template>

      <template v-slot:item.actions="{ item }">
        <v-row>
          <v-icon v-if="$store.getters.checkPermission('dealer-update')" @click="editDealer(item)" color="primary" class="mx-2">mdi-pencil</v-icon>
          <v-icon v-if="$store.getters.checkPermission('dealer-delete')" @click="deleteDealer(item.id)" color="red">mdi-delete</v-icon>
        </v-row>
      </template>
      <template v-slot:item.created_at="{ item }">
        {{ datetimeFormat(item.created_at) }}
      </template>
    </v-data-table>
    <v-dialog eager v-model="Dealer" persistent max-width="450px" @keydown.esc="Dealer = false">
      <v-card>
        <v-form ref="form">
          <v-card-title>
            <span class="headline">{{ $t("dealers.add_dealers") }}</span>
            <v-spacer></v-spacer>
            <v-btn color="red" x-small fab class @click="Dealer = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col class="pt-0" cols="12">
                  <label>{{ 'name' }}</label>
                  <v-text-field v-model="form.name" dense :rules="namerules" :error-messages="errors['name'] ? errors['name'] : []"></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ 'address' }}</label>
                  <v-text-field v-model="form.address" dense :rules="addressrules"></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ 'bac' }}</label>
                  <v-text-field :maxlength="max"  v-model="form.bac" dense :rules="bacrules" :error-messages="errors['bac'] ? errors['bac'] : []"></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ 'country' }}</label>
                  <v-text-field v-model="form.country" dense :rules="countryrules"></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions class="pt-0" >
            <v-spacer></v-spacer>
            <v-btn color="success" dark @click="save">{{ $t("save") }}</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

  </v-card>
</template>
<script>
import moment from "moment";
import Swal from "sweetalert2";


export default {
  data() {
    return{
      dataTableOptions: {
        page: 1,
        itemsPerPage: 10
      },
      page: 1,
      server_items_length: -1,
      items:[],
      max: 6,
      search: '',
      Dealer: false,
      DealerTitle: "",
      
      form: {},
      errors: [],
      namerules: [
        value => !!value || 'Dealer field is required',
      ],
      addressrules: [
        value => !!value || 'Address field is required',
      ],
      bacrules: [
        value => !!value || this.$t("dealers.bac_required"),
        value => !!value && value.length == 6 || this.$t("dealers.characters"),
        value => Number.isInteger(Number(value)) || 'The value must be an integer number',
      ],
      countryrules: [
        value => !!value || 'Country field is required',
      ],
    };
  },
  computed:{
    headers() {
      return [
        {text:"#", value:'id', align:"center"},
        {
          // text: "Organization name",
          text: this.$t("dealers.name"),
          value: "name"
        },

        {
          // text: "Business access code to GWM ()BAC",
          text: this.$t("dealers.bac"),
          value: "bac"
        },
        {
          text: this.$t("dealers.country"),
          // text: "Country name",
          value: "country"
        },
        {
          // text: "Address",
          text: this.$t("dealers.address"),
          value: "address"
        },
        {
          text: this.$t("created_at"),
          value: "created_at"
        },
        {
          text: "actions",
          align: "right",
          value: "actions",
          //sortable: false,
          width: 40,
        },
      ].filter(
        v =>
          v.value != "actions" ||
          this.$store.getters.checkPermission("dealer-update") ||
          this.$store.getters.checkPermission("dealer-delete")
      );
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
    newDealer() {
      this.Dealer = true;
      this.$refs.form.resetValidation();
      this.DealerTitle = "ADD Dealer";
      this.errors = [];
      this.form = {
        id: Date.now(),
        name: "",
        address: "",
        bac:"",
        country: "",
      };
    },
    editDealer(item) {
      this.Dealer = true;
      this.$refs.form.resetValidation();
      this.DealerTitle = this.$t("dealers.update"),
      this.form = JSON.parse(JSON.stringify(item));
      this.errors = [];
    },

    deleteDealer(id) {
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
              this.$axios.delete(
                  this.$store.state.backend_url + "api/dealers/delete/" + id
              )
                  .then(res => {
                    this.items = this.items.filter(v => v.id != id);
                    //console.log(res.data);
                  })
              Swal.fire({
                position: "top-end",
                toast: true,
                icon: "success",
                title: "Selected dealer succesfully deleted",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
              });
            }
          })
          .catch((error) => {
            console.error(error);
            Swal.fire({
              position: "center",
              icon: "error",
              width: "250px",
              title: "Failed in deleting dealer information",
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
            });
          });
    },
    save() {
      if (this.$refs.form.validate()) {
        axios
            .post(this.$store.state.backend_url + "api/dealers/update", this.form)
            .then(() => {
              this.getList();
              this.Dealer = false;
              this.errors = [];
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
              Toast.fire({
                icon: "success",
                title: "Dealer information successfully saved",
              });

            })
            .catch((err) => {
              this.errors = err.response.data.errors;
              //console.log(this.errors);
            })
      }
    },
    getList() {
      axios.post(this.$store.state.backend_url + "api/dealers",{
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
</style>