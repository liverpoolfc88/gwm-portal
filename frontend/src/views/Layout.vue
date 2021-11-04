<template>
  <v-app id="inspire">
    <v-app-bar app flat height="50" color="blue-grey lighten-4">
      <v-btn icon @click="drawer = !drawer">
        <v-icon>mdi-menu</v-icon>
      </v-btn>
      <v-spacer></v-spacer>
      <v-menu offset-y>
      </v-menu>

<!--      <img src= "/img/en-flag.png" alt="GWM" style=" width: 25px; ">-->
      <img v-if="$i18n.locale == 'en'" src= "/img/en-flag.png" alt="GWM" style=" width: 25px; ">
      <img v-if="$i18n.locale == 'ru'" src= "/img/ru-flag.png" alt="GWM" style=" width: 25px; ">
      <v-btn @click="en()" v-show="leng" text>
        {{ languages[$i18n.locale] }}
      </v-btn>
<!--      <img v-show="!leng" src="/img/ru-flag.png" alt="GWM" style=" width: 25px; "> -->
      <v-btn @click="ru()" v-show="!leng" text>
        {{ languages[$i18n.locale] }}
      </v-btn>

      <v-btn class="" icon color="blue-grey lighten-25">
        <v-icon>mdi-account-circle</v-icon>
      </v-btn>
      <span>{{ user.username }}</span>
      <v-btn icon color="red" @click="logout()">
        <v-icon>mdi-exit-to-app</v-icon>
      </v-btn>

    </v-app-bar>

    <v-navigation-drawer
        app
        v-model="drawer"
        class="drawer"
        color="blue-grey lighten-4"
    >
      <template v-slot:prepend>
        <v-sheet
            color="blue-grey lighten-4"
            class="px-6 justify-center ml-5"
            height="50"
            width="80%"
            dark
        >
          <img src="/img/motors-logo.jpg" alt="GWM" class=""
               style="height: 100%; width: 100%; padding: 5px">
          <span style="color: #0a53be" class="brand-text"></span>
        </v-sheet>
      </template>

      <v-divider></v-divider>
      <v-row>
        <v-col class="pt-10">
          <v-list class="pa-0" dense v-for="item in MenuItems" :key="item.title">
            <v-list-item
                v-if="item.visible"
                router
                :to="item.link"
            >
              <v-list-item-icon>
                <v-icon color="blue">{{ item.icon }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ item.title }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>

          <v-list dense class="dropdown pa-0">
            <v-list-group
                v-if="item.visible"
                v-for="item in items"
                :key="item.title"
                link
                :to="item.link"
            >
              <template v-slot:activator>
                <v-icon color="blue">{{ item.icon }}</v-icon>
                <v-list-item-content class="ml-8">
                  <v-list-item-title v-text="item.title"></v-list-item-title>
                </v-list-item-content>
              </template>
              <v-list-item
                  v-if="subItem.visible"
                  v-for="subItem in item.items"
                  :key="subItem.title"
                  link
                  :to="subItem.link"
                  
              >
                <v-icon color="blue">{{ subItem.icon }}</v-icon>
                <v-list-item-content class="ml-5">
                  <v-list-item-title v-text="subItem.title"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-group>
          </v-list>

        </v-col>
      </v-row>

    </v-navigation-drawer>


    <v-main class="ma-1 blue-grey lighten-5">
      <router-view></router-view>
    </v-main>

    <!--    <v-footer app color="transparent" height="72" inset>-->
    <!--      © {{ 2021 }} АО «UzAuto Motors»-->
    <!--    </v-footer>-->
  </v-app>
</template>

<script>
import Cookies from "js-cookie";

export default {
  data: () => ({
    arg: '',
    leng: true,
    locales: [
      {value: `en`, text: `English`},
      {value: `ru`, text: `Русский`},
    ],
    languages: {
      en: `English`,
      ru: `Русский`,
    },
    drawer: null,
    profile: false,
    date: new Date()
  }),
  computed: {

    user() {
      return this.storage.get('user');
    },
    MenuItems() {
      return [
        {
          title: this.$t("realizedvin.sent_data"),
          icon: "mdi-format-list-checks",
          link: "/realize",
          visible: this.$store.getters.checkPermission("sendvin-index")
        },
        {
          title: this.$t("uploadvin.upload_vin"),
          icon: "mdi-table-arrow-up",
          link: "/excel",
          visible: this.$store.getters.checkPermission("uploadvin-index")
        },
        {
          title: this.$t("uploadvin.vin"),
          icon: "mdi-table-arrow-right",
          link: "/vin",
          visible: this.$store.getters.checkPermission("showvin-index")
        },
      ]
    },
    items() {
      return [
        {
          // title: "Settings",
          title: this.$t("settings.settings"),
          icon: "mdi-cog",
          visible: this.$store.getters.checkPermission("settings-index"),
          items: [
            {
              // title: "Users list",
              title: this.$t("user.user_list"),
              icon: "mdi-account-multiple-plus",
              link: "/users",
              visible: this.$store.getters.checkPermission("users-show"),
            },
            {
              // title: "Distribution centers",
              title: this.$t("dealers.dealers"),
              icon: "mdi-sitemap",
              link: "/dealers",
              visible: this.$store.getters.checkPermission("dealers-show"),
            },
            {
              // title: "Distribution centers",
              title: this.$t("role.index"),
              icon: "mdi-account-group",
              link: "/roles",
              visible: this.$store.getters.checkPermission("roles-show"),
            },
            {
              // title: "Distribution centers",
              title: this.$t("permission.index"),
              icon: "mdi-account-multiple-check",
              link: "/permissions",
              visible: this.$store.getters.checkPermission("permissions-show"),
            },
          ],
        },
        {
          title: this.$t("filesinfo.files_info"),
          // title: "GWM files info",
          icon: "mdi-file-multiple",
          visible: this.$store.getters.checkPermission("gwmfiles-index"),
          items: [
            {
              // title: "II001 files",
              title: this.$t("filesinfo.II001"),
              icon: "mdi-file-document-outline",
              link: "/buildfiles",
              visible: this.$store.getters.checkPermission("ii001_file-show")
            },
            {
              // title: "II002 files",
              title: this.$t("filesinfo.II002"),
              icon: "mdi-file-document-outline",
              link: "/invoicefiles",
              visible: this.$store.getters.checkPermission("ii002_file-show")
            },
          ],
        },
      ];
    }
  },
  methods: {
    en() {
      // this.arg = 'en',
      this.setLocale('ru')
      this.leng = false
    },
    ru() {
      // this.arg = 'ru',
      this.setLocale('en')
      this.leng = true
    },
    setLocale: function (arg) {
      this.$i18n.locale = arg;
      this.$store.dispatch("setLocale", arg);
    },
    logout() {
      this.storage.clear();
      this.$router.push('login')
    }
  }
}
</script>

<style>
/* width */
::-webkit-scrollbar {
  width: 10px;
}

/* Track */
::-webkit-scrollbar-track {
  background: rgb(241, 241, 241);
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: rgb(206, 206, 206);
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
}

</style>
