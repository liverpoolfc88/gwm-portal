<template>
  <div>
    <v-card class="ma-1 pa-1">
      <v-card-title class="pa-1">
        <span>{{ $t('role.index') }}</span>
        <v-spacer></v-spacer>
        <v-btn
            v-if="$store.getters.checkPermission('role-create')"
            @click="newItem()"
            color="success"
            class="ml-8 pl-1 pr-5"
            dark
            small
        >
          <v-icon class="mr-3" text>mdi-plus-box</v-icon>
          {{ $t("new") }}
        </v-btn>
      </v-card-title>
      <v-data-table
        dense
        fixed-header
        :loading-text="$t('loadingText')"
        :no-data-text="$t('noDataText')"
        :height="screenHeight"
        :loading="loading"
        :headers="headers"
        :items="items"
        class="ma-1"
        style="border: 1px solid #aaa;"
        :footer-props="{
          itemsPerPageOptions: [20, 50, 100, -1],
          itemsPerPageAllText:$t('itemsPerPageAllText'),
          itemsPerPageText: $t('itemsPerPageText'),
          showFirstLastPage: true,
          firstIcon: 'mdi-arrow-collapse-left',
          lastIcon: 'mdi-arrow-collapse-right',
          prevIcon: 'mdi-arrow-left',
          nextIcon: 'mdi-arrow-right'
        }"
      >
        <template
            v-slot:item.id="{ item }"
        >{{items.map(function(x) {return x.id; }).indexOf(item.id) + 1}}</template>
        <template
          v-slot:item.id="{ item }"
        >{{items.map(function(x) {return x.id; }).indexOf(item.id) + 1}}</template>
        <template v-slot:item.description="{ item }">
          <span style="white-space:normal;max-width: 100px;">{{ item.description }}</span>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn
            v-if="$store.getters.checkPermission('manage_permissions')"
            color="green"
            small
            text
            @click="managePermissions(item)"
          >
            <v-icon>mdi-account-key-outline</v-icon>
          </v-btn>
          <v-btn
            v-if="$store.getters.checkPermission('role-update')"
            color="blue"
            small
            text
            @click="editItem(item)"
          >
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn
            v-if="$store.getters.checkPermission('role-delete')"
            color="red"
            small
            text
            @click="deleteItem(item)"
          >
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>
    <v-dialog v-model="permissionDialog" persistent max-width="800px">
      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>
          <span class="headline">{{ $t('role.dialogHeaderText') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" outlined x-small fab class @click="permissionDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col>
              <b>{{ $t('role.name') }}</b>
              : {{ permissionForm.display_name }}
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
                      >{{ $t('role.assigned_permissions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th colspan="3" class="pa-0 ma-0">
                        <v-text-field
                          v-model="assignedPermissionSearch"
                          outlined
                          dense
                          hide-details
                          clearable
                          placeholder="Search"
                          class="ma-0 pa-0"
                        ></v-text-field>
                      </th>
                    </tr>
                    <tr v-for="(item, index) in rolePermissionList" :key="index">
                      <td>{{ index+1 }}</td>
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
                          @click="removePermission(item)"
                        >mdi-minus-circle-outline</v-icon>
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
                      >{{ $t('role.not_assigned_permissions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th colspan="3" class="pa-0">
                        <v-text-field
                          v-model="notAssignedPermissionSearch"
                          outlined
                          dense
                          hide-details
                          clearable
                          placeholder="Search"
                          class="ma-0 pa-0"
                        ></v-text-field>
                      </th>
                    </tr>
                    <tr v-for="(item, index) in permissionList" :key="index">
                      <td>{{ index+1 }}</td>
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
                          @click="addPermission(item)"
                        >mdi-plus-circle-outline</v-icon>
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
          <v-btn color="primary" @click="saveRolePermission">{{ $t('save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog" @keydown.esc="dialog = false" persistent max-width="600px">
      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>
          <span class="headline">{{ $t('role.dialogHeaderText') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="red" outlined x-small fab class @click="dialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-form @keyup.native.enter="save" ref="dialogForm">
            <v-row>
              <v-col cols="12" sm="6">
                <label for>{{ $t('role.name') }}</label>
                <v-text-field
                  v-model="form.name"
                  :rules="[v => !!v || $t('input_required')]"
                  hide-details="auto"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <label for>{{ $t('role.display_name') }}</label>
                <v-text-field
                  v-model="form.display_name"
                  :rules="[v => !!v || $t('input_required')]"
                  hide-details="auto"
                  dense
                  outlined
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <label for>{{ $t('role.description') }}</label>
                <v-text-field
                  v-model="form.description"
                  :rules="[v => !!v || $t('input_required')]"
                  hide-details="auto"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
          <small color="red">{{ $t('input_required') }}</small>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="save">{{ $t('save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="loading" width="300" hide-overlay>
      <v-card color="primary" dark>
        <v-card-text>
          {{ $t('loadingText') }}
          <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import Swal from "sweetalert2";
export default {
  data() {
    return {
      assignedPermissionSearch: "",
      notAssignedPermissionSearch: "",
      permissionDialog: false,
      loading: false,
      search: "",
      dialog: false,
      editMode: null,
      items: [],
      permissions: [],
      form: {},
      permissionForm: { permissions: [] },
      dialogHeaderText: "",
      createdAtMenu2: false
    };
  },
  computed: {
    screenHeight() {
      return window.innerHeight - 170;
    },
    permissionList() {
      return this.permissions
        .filter(v => {
          return !this.permissionForm.permissions.find(p => p.id == v.id);
        })
        .filter(v =>
          this.notAssignedPermissionSearch
            ? v.display_name
                .toUpperCase()
                .search(this.notAssignedPermissionSearch.toUpperCase()) >= 0
            : true
        );
    },
    rolePermissionList() {
      return this.permissionForm.permissions.filter(v =>
        this.assignedPermissionSearch
          ? v.display_name
              .toString()
              .search(this.assignedPermissionSearch.toUpperCase()) >= 0
          : true
      );
    },
    headers() {
      return [
        { text: "#", value: "id", align: "center", width: 30 },
        {
          text: this.$t("role.name"),
          value: "name",
          width: 200
        },
        {
          text: this.$t("role.display_name"),
          value: "display_name",
          width: 200
        },
        {
          text: this.$t("role.description"),
          value: "description",
          width: 200
        },
        {
          text: this.$t("actions"),
          value: "actions",
          width: 60,
          align: "center"
        }
      ].filter(
        v =>
          v.value != "actions" ||
          this.$store.getters.checkPermission("role-update") ||
          this.$store.getters.checkPermission("role-delete")
      );
    }
  },
  methods: {
    addPermission(item) {
      this.permissionForm.permissions.push(item);
    },
    removePermission(item) {
      this.permissionForm.permissions = this.permissionForm.permissions.filter(
        v => v.id != item.id
      );
    },
    getList() {
      this.loading = true;
      axios
        .get(this.$store.state.backend_url + "api/roles")
        .then(response => {
          this.items = response.data;
          this.loading = false;
          this.getRef();
        })
        .catch(error => {
          console.log(error);
          this.loading = false;
        });
    },
    getRef() {
      this.loading = true;
      axios
        .get(this.$store.state.backend_url + "api/roles/get-ref")
        .then(response => {
          this.permissions = response.data.permissions.map(v => {
            v.group = v.name.split("-")[0];
            return v;
          });
          this.loading = false;
        })
        .catch(error => {
          console.log(error);
          this.loading = false;
        });
    },
    managePermissions(item) {
      this.permissionForm = item;
      this.permissionDialog = true;
    },
    newItem() {
      if (this.$store.getters.checkPermission("role-create")) {
        this.dialogHeaderText = this.$t("role.newOrganization");
        this.form = {
          id: Date.now(),
          name: "",
          display_name: "",
          description: ""
        };
        this.dialog = true;
        this.editMode = false;
        if (this.$refs.dialogForm) this.$refs.dialogForm.reset();
      }
    },
    editItem(item) {
      if (this.$store.getters.checkPermission("role-update")) {
        this.dialogHeaderText = this.$t("role.updaterole");
        this.formIndex = this.items.indexOf(item);
        this.form = Object.assign({}, item);
        this.dialog = true;
        this.editMode = true;
        if (this.$refs.dialogForm) this.$refs.dialogForm.resetValidation();
      }
    },
    saveRolePermission() {
      axios
        .post(
          this.$store.state.backend_url + "api/roles/update-role-permission",
          this.permissionForm
        )
        .then(res => {
          this.getList();
          this.permissionDialog = false;
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
          console.log(err);
        });
    },
    save() {
      if (this.$refs.dialogForm.validate())
        axios
          .post(this.$store.state.backend_url + "api/roles/update", this.form)
          .then(res => {
            this.getList();
            this.dialog = false;
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
            console.log(err);
          });
    }, //document-types
    deleteItem(item) {
      if (this.$store.getters.checkPermission("role-delete")) {
        const index = this.items.indexOf(item);
        Swal.fire({
          title: this.$t("swal_title"),
          text: this.$t("swal_text"),
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: this.$t("swal_delete")
        }).then(result => {
          if (result.value) {
            axios
              .delete(
                this.$store.state.backend_url + "api/roles/delete/" + item.id
              )
              .then(res => {
                this.getList(this.page, this.itemsPerPage);
                this.dialog = false;
                Swal.fire("Deleted!", this.$t("swal_deleted"), "success");
              })
              .catch(err => {
                Swal.fire({
                  icon: "error",
                  title: this.$t("swal_error_title"),
                  text: this.$t("swal_error_text")
                  //footer: "<a href>Why do I have this issue?</a>"
                });
                console.log(err);
              });
          }
        });
      }
    }
  },
  mounted() {
    this.getList();
  },
  created() {}
};
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