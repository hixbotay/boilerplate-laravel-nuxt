<template>
  <v-app id="default" :dark="isDark">
    <SeoHead />
    <v-navigation-drawer v-model="drawer" :mini-variant="miniVariant" :dark="isDark" fixed app floating
      styles="height: calc(100% - 2rem); top: 0px; max-height: calc(100% - 0px)">
      <nuxt-link to="/" class="no-underline success--text">
        <v-flex align-self-center class="text-center justify-center align-center" styles="height: 33px">
          <img class="my-6 w-100 d-block" src="/logo.png"/>
        </v-flex>
      </nuxt-link>
      <MenuAdmin :isDark="isDark"/>
    </v-navigation-drawer>
    <v-app-bar :color="isDark ? 'default' : 'white'" :dark="isDark" elevation="8" app class="px-4" elevate-on-scroll>
      <v-app-bar-nav-icon :class="drawer ? '' : 'menu-toggle'" @click.stop="drawer = !drawer" />
      <v-btn small icon :class="drawer ? '' : 'd-none'" @click.stop="miniVariant = !miniVariant">
        <v-icon>mdi-{{ `chevron-${miniVariant ? 'right' : 'left'}` }}</v-icon>
      </v-btn>
      <v-spacer />
      <v-col align-self="center" :cols="searchLength ? '7' : '3'" :sm="searchLength ? '3' : '2'"
        :lg="searchLength ? '2' : '1'">
        <v-text-field v-model="search" :full-width="true" :hide-details="true" :height="`100%`"
          :placeholder="searchLength ? 'Search' : ''" name="search" prepend-inner-icon="mdi-magnify" maxlength="20" dense
          solo flat @focus="searchFocus" @focusout="searchFocus">
        </v-text-field>
      </v-col>
      <v-btn small icon @click.stop="setTheme">
        <v-icon>mdi-invert-colors</v-icon>
      </v-btn>
      <NuxtLink v-if="!user.id" to="/login" class="mr-1 v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--small">Đăng nhập</NuxtLink>
      <NuxtLink v-if="!user.id" to="/register" class="mr-1 v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--small">Đăng ký</NuxtLink>
      
      <v-menu transition="slide-y-transition" bottom top >
        <template v-slot:activator="{ on, attrs }">
          <v-btn v-show="user.id" small icon v-bind="attrs" v-on="on">
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>
        <v-list class="pl-2 pr-4">
          <v-list-item :to="`/admin/profile`">
            <v-list-item-icon>
              <v-icon>mdi-account-circle-outline</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title> Profile </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item @click="logout">
            <v-list-item-icon>
              <v-icon>mdi-logout-variant</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title> Logout </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>
    <v-main>
      <v-container fluid>
        <nuxt />
      </v-container>
    </v-main>
    <v-btn v-show="fab" v-scroll="onScroll" fab light fixed bottom right small color="error" @click="toTop">
      <v-icon>mdi-arrow-up</v-icon>
    </v-btn>
    <v-overlay v-if="$nuxt.isOffline" :value="overlay" z-index="1000">
      <v-banner color="warning" single-line>
        <v-icon slot="icon" color="light" size="36">
          mdi-wifi-strength-alert-outline
        </v-icon>
        <span class="white--text">Unable to verify your Internet connection</span>
        <template v-slot:actions>
          <v-btn class="white--text" color="warning" text flat fab @click="overlay = !overlay">
            <v-icon> mdi-window-close </v-icon>
          </v-btn>
        </template>
      </v-banner></v-overlay>
      <ModalError/>
  </v-app>
</template>

<script>
import { mapState } from 'vuex'
import global from '~/constants/global'
import SeoHead from '~/components/SeoHead'
import MenuAdmin from '~/components/MenuAdmin.vue'
import ModalError from '~/components/ModalError.vue'
import Purchase from '~/components/Purchase.vue'
import AuthUtil from '~/utils/AuthUtil'
import LocalStorageHelper from '~/utils/LocalStorageHelper'
import JsCoreHelper from '~/utils/JsCoreHelper'

export default {
  name: 'LayoutDefault',
  components: { SeoHead, MenuAdmin, Purchase, ModalError },
  data() {
    return {
      modalPurchase: false,
      title: 'Tài nguyên MMO',
      company: global.company,
      drawer: false,
      overlay: true,
      miniVariant: false,
      right: true,
      fab: true,
      darkmode: false,
      searchLength: false,
      search: '',
      user: {id:false}
    }
  },
  head() {
    return {
      bodyAttrs: {
        class: this.$vuetify.theme.isDark ? 'theme--dark' : 'theme--light'
      }
    }
  },
  computed: {
    ...mapState({ isDark: (state) => state.core.theme.isDark })
  },
  async created() {
    const {
      $isServer,
      $store: { commit }
    } = this
    // Execute initial stores
    if (!$isServer) {
      commit('core/INITIALIZE_STORE')
    }
    this.user = await AuthUtil.getCurrentUser()

  },
  methods: {
    setTheme() {
      const {
        $vuetify,
        $store: { dispatch }
      } = this
      $vuetify.theme.dark = !$vuetify.theme.isDark
      const nuxtify = JSON.parse(localStorage.getItem('nuxtify') || 'null')
      dispatch('core/setDark', {
        ...nuxtify,
        isDark: $vuetify.theme.dark
      })
    },
    searchFocus() {
      this.searchLength = !this.searchLength
      this.search = ''
    },
    onScroll(e) {
      if (typeof window === 'undefined') return
      const top = window.pageYOffset || e.target.scrollTop || 0
      this.fab = top > 20
    },
    toTop() {
      const { $vuetify } = this
      $vuetify.goTo(0)
    },
    logout(){
      AuthUtil.logout()
      this.user={}
    }
  }
}
</script>
<style lang="scss" scoped>

</style>