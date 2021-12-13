<template>
  <v-card class="ma-2 pl-3 pr-3 pb-3">
    <v-card-title class="pa-1">
      {{ $t("user.user_list") }}
      <v-spacer></v-spacer>
      <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          :placeholder="$t('search')"
          single-line
          hide-details
          dense
      ></v-text-field>
      <v-btn v-if="$store.getters.checkPermission('user-create')"
             @click="newUser()" color="success" class="ml-8 pl-1 pr-5" dark small>
        <v-icon class="mr-3" text>mdi-plus-box</v-icon>
        {{ $t("new") }}
      </v-btn>
    </v-card-title>
    <v-data-table
        dense
        fixed-header
        :headers="headers"
        :items="users"
        :options.sync="dataTableOptions"
        :search="search"
        item-key="id"
        :server-items-length="server_items_length"
        class="elevation-5"
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
      >{{
          users.map(function (x) {
            return x.id;
          }).indexOf(item.id) + 1
        }}
      </template>
      <template v-slot:[`item.roles`]="{ item }">
          <span v-for="(item, idxRole) in item.roles" :key="idxRole">{{
              item.name + ", "
            }}</span>
      </template>
      <template v-slot:item.action="{ item }">
        <v-row>
          <v-icon v-if="$store.getters.checkPermission('user-update')" @click="editUser(item)" color="primary"
                  class="mx-2">mdi-pencil
          </v-icon>
          <v-icon v-if="$store.getters.checkPermission('user-delete')" @click="deleteUser(item.id)" color="red">
            mdi-delete
          </v-icon>
        </v-row>
      </template>
      <template v-slot:item.password="{ item }">
        <v-icon @click="editPassword(item)" color="primary" class="mx-2">mdi-lock-minus</v-icon>
      </template>
      <template v-slot:item.roles="{ item }">
        <v-icon v-if="$store.getters.checkPermission('manage_roles')" @click="editRole(item)" color="primary"
                class="mx-2">mdi-account-multiple-plus-outline
        </v-icon>
      </template>
    </v-data-table>
    <v-dialog eager v-model="UserModal" persistent max-width="450px" @keydown.esc="UserModal = false">
      <v-card>
        <v-card-title>
          <span class="headline">{{ $t('user.add_user') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="UserModal = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-form ref="form">
            <v-container>
              <v-row>
                <v-col class="pt-0" cols="12">
                  <label>{{ $t('user.name') }}</label>
                  <v-text-field v-model="form.name" dense :rules="[rules.required]"></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ $t('user.email') }} </label>
                  <v-text-field
                      v-model="form.email"
                      dense
                      :error-messages="errors['email'] ? errors['email'] : []"
                      :rules="emailRules"

                  ></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ $t('user.username') }}</label>
                  <v-text-field v-model="form.username" dense :rules="loginRules" :error-messages="errors['username'] ? errors['username'] : []"></v-text-field>
                </v-col>

                <v-col v-if="(password_show == true) ? false : true" class="pt-0" cols="12">
                  <label>{{ $t('user.password') }}</label>
                  <v-text-field
                      v-model="form.password"
                      dense
                      :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
                      :rules="[rules.required, rules.min]"
                      :type="show ? 'text' : 'password'"
                      name="input-10-1"
                      hint="At least 8 characters"
                      counter
                      @click:append="show = !show"
                  ></v-text-field>
                </v-col>
                <v-col class="pt-0" cols="12">
                  <label>{{ $t('dealers.dealers') }}</label>
                  <v-autocomplete
                      v-model="form.dealer_id"
                      :items="dealers"
                      item-text="name"
                      item-value="id"
                      :rules="[rules.required]"
                  >
                    <template v-slot:selection="data">
                      <v-list-item-content>
                        <v-list-item-title v-html="data.item.name"></v-list-item-title>
                      </v-list-item-content>
                    </template>
                    <template v-slot:item="data">
                      <template>
                        <v-list-item-content>
                          <v-list-item-title v-html="data.item.name"></v-list-item-title>
                        </v-list-item-content>
                      </template>
                    </template>
                  </v-autocomplete>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>

        <v-card-actions class="pt-0">
          <v-spacer></v-spacer>
          <v-btn color="green" @click="save" dark>{{ $t('save') }}</v-btn>
          <!--                        <v-btn color="red darken-1" dark @click="onClickOutside">{{ $t('close') }}</v-btn>-->
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="Userpssword" persistent max-width="450px" @keydown.esc="Userpssword = false">
      <v-card>
        <v-card-title>
          <span class="headline">{{ $t('user.password') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" x-small fab class @click="Userpssword = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-form ref="formPassword">
            <v-container>
              <v-row>
                <v-col class="pt-0" cols="12">
                  <label>{{ $t('user.password') }}</label>
                  <v-text-field
                      v-model="form_p.password"
                      dense
                      :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
                      :rules="[rules.required, rules.min]"
                      :type="show ? 'text' : 'password'"
                      name="input-10-1"
                      hint="At least 8 characters"
                      counter
                      @click:append="show = !show"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>

        <v-card-actions class="pt-0">
          <v-spacer></v-spacer>
          <v-btn color="green" @click="save_p" dark>{{ $t('save') }}</v-btn>
          <!--                        <v-btn color="red darken-1" dark @click="onClickOutside">{{ $t('close') }}</v-btn>-->
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogRole" persistent max-width="800px">
      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>
          <span class="headline">{{ $t('role.dialogHeaderText') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" outlined x-small fab class @click="dialogRole = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col>
              <b>{{ $t('user.name') }}</b>
              : {{ roleForm.name }}
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6" class="pa-1">
              <v-simple-table dense fixed-header height="500px">
                <template v-slot:default>
                  <thead>
                  <tr>
                    <th class="font-weight-black">#</th>
                    <th
                        class="text-left font-weight-black"
                        colspan="3"
                    >{{ $t('role.assigned_permissions') }}
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <th colspan="3" class="pa-0 ma-0">
                      <v-text-field
                          v-model="notAssignedRoleSearch"
                          outlined
                          dense
                          hide-details
                          clearable
                          placeholder="Search"
                          class="ma-0 pa-0"
                      ></v-text-field>
                    </th>
                  </tr>
                  <tr v-for="(item, index) in userRoleList" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td :title="item.display_name">
                      <p
                          class="text-truncate ma-0"
                          style="max-width:280px;"
                      >{{ item.display_name }}</p>
                    </td>
                    <td class="pa-0">
                      <v-icon
                          color="error"
                          class="my-1"
                          @click="removeRole(item)"
                      >mdi-minus-circle-outline
                      </v-icon>
                    </td>
                  </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-col>
            <v-col cols="12" sm="6" class="pa-1">
              <v-simple-table dense fixed-header height="500px">
                <template v-slot:default>
                  <thead>
                  <tr>
                    <th class="font-weight-black">#</th>
                    <th
                        class="text-left font-weight-black"
                        colspan="3"
                    >{{ $t('role.not_assigned_permissions') }}
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <th colspan="3" class="pa-0">
                      <v-text-field
                          v-model="notAssignedRoleSearch"
                          outlined
                          dense
                          hide-details
                          clearable
                          placeholder="Search"
                          class="ma-0 pa-0"
                      ></v-text-field>
                    </th>
                  </tr>
                  <tr v-for="(item, index) in roleList" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td :title="item.display_name">
                      <p
                          class="text-truncate ma-0"
                          style="max-width:280px;"
                      >{{ item.display_name }}</p>
                    </td>
                    <td class="pa-0">
                      <v-icon
                          color="primary"
                          class="my-1"
                          @click="addRole(item)"
                      >mdi-plus-circle-outline
                      </v-icon>
                    </td>
                  </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-col>
          </v-row>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="saveRole">{{ $t('save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="loading" width="300" hide-overlay>
      <v-card color="primary" dark>
        <v-card-text>
          {{ $t("loadingText") }}
          <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-card>
</template>
<script>
// const moment = require("moment");
// import moment from 'moment';
import Swal from "sweetalert2";

export default {
  data() {
    return {
      // Loading: false,
      dataTableOptions: {
        page: 1,
        itemsPerPage: 10
      },
      page: 1,
      loading: false,
      server_items_length: -1,
      UserModal: false,
      dialogHeaderText: "",
      Userpssword: false,
      UserTitle: "",
      password_show: false,
      users: [],
      dialogRole: false,
      roles: [],
      dealers: [],
      notAssignedRoleSearch: "",
      AssignedRoleSearch: "",
      search: "",
      email: '',
      form: {},
      roleForm: {roles: []},
      form_p: {},
      show: false,
      errors: [],
      emailRules: [
        v => !!v || this.$t('user.email_required'),
        //v => /.+@.+\..+/.test(v) || this.$t('user.email_valid'),
        v => !!v && v.length > 0 && /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/.test(v) || 'Invalid e-mail.'
      ],
      rules: {
        required: value => !!value || this.$t('user.required'),
        min: value => !!value && value.length >= 8 || this.$t('user.min_char'),
        // emailMatch: () => (`The email and password you entered don't match`),
      },
      loginRules: [
        v => !!v || 'Login field is required',
      ]
    }
  },
  computed: {
    user() {
      return this.storage.get('user');
    },
    headers() {
      return [

        {text: '#', value: 'id'},
        {text: this.$t("user.name"), value: 'name'},
        {text: this.$t("user.username"), value: 'username'},
        {text: this.$t("user.email"), value: 'email'},
        // {text: this.$t("user.role"), value: 'roles'},
        {text: this.$t("dealers.dealers"), value: 'dealers.name'},
        {
          text: this.$t("action"),
          align: "right",
          value: "action",
          sortable: false,
          width: 100,
          class: "text-start"
        },
        {
          // text: "password setting",
          text: this.$t("user.password_setting"),
          align: "right",
          value: "password",
          sortable: false,
          width: 40,
          class: "text-start"
        },
        {
          text: this.$t("roles"),
          align: "right",
          value: "roles",
          sortable: false,
          width: 40,
          class: "text-start"
        },
      ];
    },
    roleList() {
      return this.roles
          .filter(v => {
            return !this.roleForm.roles.find(p => p.id == v.id);
          })
          .filter(v =>
              this.notAssignedRoleSearch
                  ? v.display_name
                  .toUpperCase()
                  .search(this.notAssignedRoleSearch.toUpperCase()) >= 0
                  : true
          );
    },
    userRoleList() {
      return this.roleForm.roles.filter(v =>
          this.AssignedRoleSearch
              ? v.display_name
              .toString()
              .search(this.AssignedRoleSearch.toUpperCase()) >= 0
              : true
      );
    },
  },
  methods: {
    addRole(item) {
      this.roleForm.roles.push(item);
    },
    removeRole(item) {
      this.roleForm.roles = this.roleForm.roles.filter(
          v => v.id != item.id
      );
    },
    updatePage($event) {
      this.getUserlist();
    },
    updatePerPage($event) {
      this.getUserlist();
    },
    newUser() {
      this.UserModal = true;
      this.UserTitle = "ADD User";
      this.errors = [];
      this.form = {
        id: Date.now(),
        name: "",
        username: "",
        email: "",
        password: "",
        role_id: "",
        dealer_id: ''
      };
      // console.log(this.$refs);
      this.$refs.form.resetValidation();
    },
    editPassword(item) {
      this.Userpssword = true;
      this.UserTitle = "ADD User";
      this.form_p = {
        id: item.id,
        password: ""
      };
    },
    // role
    editRole(item) {
      this.roleForm = item;
      this.dialogRole = true;
    },
    saveRole() {
      axios
          .post(
              this.$store.state.backend_url + "api/users/update-role",
              this.roleForm)
          .then(res => {
            this.dialogRole = false;
            this.getUserlist();
            const Toast = Swal.mixin({
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true,
              onOpen: toast => {
                toast.addEventListener("mouseenter", Swal.stopTimer);
                toast.addEventListener("mouseleave", Swal.resumeTimer);
              }
            });

            Toast.fire({
              icon: "success",
              title: this.$t("create_update_operation")
            });
          })
          .catch(err => {
            console.log(err.response);
          });
    },
    //role
    editUser(item) {
      this.UserModal = true;
      this.password_show = true;
      this.errors = [];
      this.UserTitle = "Update User";
      this.form = JSON.parse(JSON.stringify(item));
    },
    deleteUser(id) {
      Swal.fire({
        title: this.$t('delete'),
        text: this.$t('user.swal_txt'),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: this.$t('delete'),
        cancelButtonText: this.$t('close'),
      })
          .then((result) => {
            if (result.value) {
              this.$axios.delete(
                  this.$store.state.backend_url + "api/users/delete/" + id
              )
                  .then(res => {
                    this.users = this.users.filter(v => v.id != id);
                  })
              Swal.fire({
                position: "top-end",
                toast: true,
                icon: "success",
                title: this.$t('Selected user succesfully deleted'),
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
              title: "swal_error_text",
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
            });
          });
    },

    save() {
      if (this.$refs.form.validate()) {
        this.$axios
            .post(this.$store.state.backend_url + "api/users/update", this.form)
            .then((v) => {
              this.getUserlist();
              this.UserModal = false;
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
                title: "create_update_operation",
              });
            })
            .catch((e) => {
              this.errors = e.response.data.errors;
              // let text = e.response.data.message;
              // if (text.includes('users_email_unique')) {
              //   this.email_unique = true;
              // }
            })
      }
    },

    save_p() {
      if (this.$refs.formPassword.validate()) {
        this.$axios
            .post(this.$store.state.backend_url + "api/users/password", this.form_p)
            .then((v) => {
              this.getUserlist();
              this.Userpssword = false;
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
                title: "User`s password successfully changed",
              });
            })
      }
    },

    getDealer() {
      this.$axios
          .get(this.$store.state.backend_url + "api/deals")
          .then((resp) => {
            this.dealers = resp.data.data;
            console.log(resp);
          })
          .catch(function (error) {
            console.log(error);
          });
    },
    getRole() {
      this.$axios
          .get(this.$store.state.backend_url + "api/role")
          .then((respon) => {
            this.roles = respon.data;
          })
          .catch(function (error) {
            console.log(error);
          });
    },
    getUserlist() {
      // this.Loading = true;
      this.$axios
          .post(this.$store.state.backend_url + "api/users", {
            pagination: this.dataTableOptions
          })
          .then((res) => {
            this.server_items_length = res.data.total;
            this.users = res.data.data;
          })
          .catch(function (error) {
            console.log(error);
            // this.loading = false;
          });
    }
  },
  mounted() {
    this.getUserlist();
    this.getDealer();
    this.getRole();
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

.v-data-table-header th {
  background-color: #CEE2F7 !important;
  color: black !important;
}

</style>