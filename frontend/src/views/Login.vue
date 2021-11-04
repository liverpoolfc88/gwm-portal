<template>
  <div>
    <v-app style="
    background-image: url(../img/fon.png);
    /*background-position: center bottom;*/
    background-attachment: fixed;
    background-size: 100%;
    /*position: relative;*/
">
      <v-card style="background-color: white" elevation="15" class="mx-auto mt-8" max-width="400" color="">
        <v-card-text>
          <v-row>
            <v-form @keyup.native.enter="login()" ref="form">
              <div class="px-6 justify-center"              >
                <img  src="/img/motors-logo.jpg" alt="GWM" class="justify-center"
                      style=" width: 100%; margin-top:20px;">
                <span style="color: #0a53be" class="brand-text"></span>
              </div>
              <v-col cols="12">
                <v-text-field v-model="form.username" label="Username" :rules="userRules" :error-messages="errors ? errors : []"></v-text-field> 
              </v-col>
              <v-col cols="12">
                <v-text-field type="password" v-model="form.password" label="Password" :rules="passwordRules" :error-messages="errors ? errors : []"></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-btn color="blue-grey darken-3" @keyup.enter="login()" dark @click="login()">Login</v-btn>
              </v-col>
            </v-form>
          </v-row>
        </v-card-text>
      </v-card>
    </v-app>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      form:{
        username: '',
        password: '',
      },
      errors: [],
      userRules: [
        v => !!v || 'User field is required'
      ],
      passwordRules: [
        v => !!v || 'Password field is required'
      ],
    }
  },
  // data: () => ({
  //   username: '',
  //   password: '',
  // }),
  methods: {
    login(){
      if (this.$refs.form.validate()) {
        axios.post(this.$store.state.backend_url+'oauth/token',{
          'grant_type': 'password',
          'client_id': '2',
          'client_secret': '6i4Y0T5QFICTuxUo0052xMPDN6QEDbuGkyRdhcHl',
          'username': this.form.username,
          'password': this.form.password,
          'scope': '',
        })
        .then(res => {
          this.storage.set('access_token','Bearer '+res.data.access_token);
          this.getUser();
        })
        .catch( (e) => {
          this.errors = [e.response.data.message];
        });
      }
    },
    getUser(){
      axios.get(this.$store.state.backend_url+'api/users/show')
          .then(v => {
            this.storage.set('user',v.data);
            this.$router.push('/');

            let permissions = v.data.roles
              .reduce(
                (accumulator, currentValue) =>
                  accumulator.concat(currentValue.permissions),
                []
              )
              .map(v => v.name);
            v.data.permissions.forEach(element => {
                permissions.push(element.name);
            });
            this.$store.dispatch("setPermissions", permissions);
            
          });
    }
  }
}
</script>

<style scoped>
.v-card {
  transition: opacity 0.4s ease-in-out;
}

.v-card:not(.on-hover) {
  opacity: 0.8;
}

.show-btns {
  color: rgba(255, 255, 255, 1) !important;
}
</style>
