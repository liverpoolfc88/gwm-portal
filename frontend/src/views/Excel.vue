<template>
  <v-card class="ma-2 pa-3" :disabled="loading">
    <v-card-title class="pb-0">
<!--      <span class="ml-5">Upload VIN</span>-->
      <span class="ml-5">{{ $t("uploadvin.upload_vin") }}</span>
      <v-spacer></v-spacer>
      <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          :placeholder="$t('search')"
          single-line
          hide-details
          class="mr-5"
          dense
      ></v-text-field>
      <v-file-input
          v-model="files"
          class="mr-5"
          :disabled = "disabled"
          @change="upload"
          show-size
          accept=".xls*"
          :label="$t('file_input')"
      ></v-file-input>
      <template>
        <span>{{ $t("example_file") }}</span>
        <a style="text-decoration: none !important;" class="ml-5" href="/example/example.xlsx">
          <v-btn>
            <v-icon color="success">mdi-file-excel</v-icon>
            <span  >{{$t('file')}}</span>
          </v-btn>
        </a>
      </template>
<!--      <v-btn v-if="$store.getters.checkPermission('uploadvin-create')"-->
<!--          class="mr-5" @click="onBUttonClick">-->
<!--        <v-icon color="success">mdi-file-excel</v-icon>-->
<!--        {{ $t("choose") }}-->
<!--      </v-btn>-->
<!--      <label  v-show="false">-->
<!--        <input-->
<!--            type="file"-->
<!--            id="file"-->
<!--            ref="file"-->
<!--            v-on:change="handleFileUpload()"-->
<!--            accept=".xls*"-->
<!--        />-->
<!--      </label>-->
<!--      <v-btn v-if="$store.getters.checkPermission('uploadvin-create')"-->
<!--          v-show="show_view" @click="viewFile">-->
<!--        <v-icon color="success" left>mdi-eye</v-icon>-->
<!--        {{ $t("view") }}-->
<!--      </v-btn>-->
      <v-btn v-if="$store.getters.checkPermission('uploadvin-create')"
          class="mr-5" v-show="show" @click="submitFile">
        <v-icon color="success" left>mdi-upload</v-icon>
        {{ $t("upload") }}
      </v-btn>
      <v-btn v-if="$store.getters.checkPermission('uploadvin-create')"
          v-show="show" @click="clearFile">
        <v-icon color="success" left>mdi-layers-off</v-icon>
        {{ $t("clear") }}
      </v-btn>
      <!--      <v-btn   @click="newVin()" color="success" class="ml-8" dark outlined small icon>-->
      <!--        <v-icon text>mdi-file-send-outline</v-icon>-->
      <!--      </v-btn>-->
    </v-card-title>

    <v-data-table
        :loading-text="$t('loadingText')"
        :no-data-text="$t('noDataText')"
        :loading="loading"
        dense
        disable-sort
        :headers="headers"
        :items="items"
        :search="search"
        item-key="name"
        class="elevation-5"
        fixed-header
        single-expand
    >
      <template v-slot:item.pencil="{ item }">
        <v-icon @click="editExcel(item)" color="primary" class="mx-2">mdi-pencil-box-multiple</v-icon>
      </template>
      <template  v-slot:item.vin_gm="{ item }">
        <tr>
          <td v-if="(item.validate != 1)">
          {{ item.vin_gm }}
          </td>
          <td v-if="item.validate == 1" style="color: red">
          {{ item.vin_gm }}
          </td>
        </tr>
      </template>
      <template  v-slot:item.vin_local="{ item }">
        <tr>
          <td v-if="item.validate != 2">
          {{ item.vin_local }}
          </td>
          <td v-if="item.validate == 2" style="color: red">
          {{ item.vin_local }}
          </td>
        </tr>
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


    <v-dialog v-model="errorDialog" persistent max-width="450px" @keydown.esc="errorDialog = false">
      <v-card>
        <v-card-title>
          <span class="headline"> {{ $t("error_file_title") }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="errorDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <h2>{{ error_filetemp }}</h2>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="errorUniqueVin" persistent max-width="450px" @keydown.esc="errorUniqueVin = false">
      <v-card>
        <v-card-title>
          <span class="headline"> {{ $t("error_file_title") }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="errorUniqueVin = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <h2>{{ $t("errorUniqueVin") }}</h2>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="ExcelData" persistent max-width="450px" @keydown.esc="ExcelData = false">
      <v-card>
        <v-card-title>
          <span class="headline">{{ "Update Vins" }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="ExcelData = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col v-if="form.validate == 1" class="pt-0" cols="12">
                <label>{{ 'vin_gm' }}</label>
                <v-text-field :maxlength="max" v-model="form.vin_gm" dense :rules="vinRules"></v-text-field>
              </v-col>
              <v-col v-if="form.validate == 2" class="pt-0" cols="12">
                <label>{{ 'vin_local' }}</label>
                <v-text-field :maxlength="max" v-model="form.vin_local" dense :rules="vinRules"></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions class="pt-0" >
          <v-spacer></v-spacer>
          <v-btn  color="green" dark @click="save">{{ 'save' }}</v-btn>
          <!--                        <v-btn color="red darken-1" dark @click="onClickOutside">{{ $t('close') }}</v-btn>-->
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-card>
</template>
<script>
import Swal from "sweetalert2";

export default {
  data() {
    return {
      items: [],
      // tableData:'',
      search: '',
      disabled:false,
      max: 17,
      files: null,
      loading: false,
      show: false,
      errorDialog: false,
      error_filetemp:'',
      errorUniqueVin: false,
      show_view: true,
      ExcelData:false,
      form: {},
      vinRules: [
        v => !!v || 'Vin is required',
        v => /^[a-zA-Z0-9]+$/.test(v) || 'Error-not written in Latin',
        v => v.length == 17 || '17 characters'
      ],
    }
  },
  computed: {
    headers() {
      return [
        // {text:"#", value:'id', align:"center"},
        {
          text: "",
          align: "right",
          value: "pencil",
          sortable: false,
          width: 40,
        },
        // {
        //   text: this.$t("uploadvin.skd_plant"),
        //   // Plant code, Kod zavoda
        //   value: "skd_plant",
        //   width: 15
        // },
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
    upload($event){
      // console.log(event);
      this.loading = true;
      let formData = new FormData();
      formData.append("file", this.files);
      axios
          .post(
              this.$store.state.backend_url + "api/excel/upload",
              formData,
              {}
          )
          .then((res) => {
            this.loading = false;
            this.getList();
            // this.files = null;
            this.disabled = true;
          })
          .catch(err => {
            if (err.response.status == 422){
              this.loading = false;
              this.errorDialog = true;
              this.files = null;
              this.error_filetemp = err.response.data.errors[0][0];
            }
            if (err.response.status == 500){
              this.loading = false;
              this.errorDialog = true;
              this.files = null;
              this.error_filetemp = err.response.data.message;
            }
            // console.log(err.response.data.message);

          });
    },

    // onBUttonClick() {
    //   this.$refs.file.click();
    // },
    // handleFileUpload() {
    //   this.$refs.file.click();
    //   this.file = this.file.files[0];
    // },

    editExcel(item){
      this.ExcelData = true
      this.form = {
        id: item.id,
        vin_gm: item.vin_gm,
        vin_local: item.vin_local,
        validate:item.validate
      };
    },
    save() {
      axios
          .post(this.$store.state.backend_url + "api/excelvin/update", this.form)
          .then(() => {
            this.getList();
            this.ExcelData = false;
            const Toast = Swal.mixin({
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 1000,
              timerProgressBar: true,
              onOpen: (toast) => {
                toast.addEventListener("mouseenter", Swal.stopTimer);
                toast.addEventListener("mouseleave", Swal.resumeTimer);
              },
            });
            Toast.fire({
              icon: "success",
              title: "The reference is added to the base",
            });

          })
    },

    submitFile() {
      this.loading = true;
      axios
          .post(
              this.$store.state.backend_url + "api/producedvin")
          .then((res) => {
            Swal.fire({
              position: "center",
              icon: "success",
              width: "250px",
              title: this.$t("create_update_operation"),
              showConfirmButton: false,
              timer: 1000,
              timerProgressBar: true,
            });
            this.getList();
            this.loading = false;
            this.disabled = false;
            this.files = null;
          })
          .catch((err) => {let text = err.response.data.message;
            if ((text.includes('produced_vins_vin_gm_unique')) || (text.includes('produced_vins_vin_local_unique'))){
              this.loading = false;
              this.errorUniqueVin = true;
              this.files = null;
              this.getList();
            }
          });
    },
    clearFile(){
      this.loading = true;
      axios
          .get( this.$store.state.backend_url + "api/excel/clear")
          .then((res) => {
            this.getList();
            this.loading = false;
            this.disabled = false;
            this.files = null;

          })
          .catch((err) => {
            console.log(err.message);
          });
    },
    viewFile() {
      // this.loading = true;
      // let formData = new FormData();
      // formData.append("file", this.files);
      // axios
      //     .post(
      //         this.$store.state.backend_url + "api/excel/upload",
      //         formData,
      //         {}
      //     )
      //     .then((res) => {
      //       this.loading = false;
      //       this.getList();
      //       this.files = null;
      //     })
      //     .catch((err) => {
      //       console.log(err.message);
      //
      //     });
    },
    getList() {
      axios.get(this.$store.state.backend_url + "api/producedtempvin")
          .then((res) => {
            this.items = res.data;
            if (res.data.length != 0) {
              this.show_view = false;
              this.show = true;
            } else {
              this.show_view = true;
              this.show = false;
            }
          })
          .catch((error) => {
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

.daf_bol {
  display: none;
}
</style>
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

/*.v-data-table-header {*/
/*    color: white;*/
/*}*/
</style>