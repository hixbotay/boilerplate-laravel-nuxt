<template>
  <div>
    <div class="d-flex justify-center align-center min-h-100vh">
      <v-flex lg6>
        <v-row no-gutters>
          <v-container fluid class="pb-12 text-center">
            <h1 class="color-white">Đăng kí tài khoản mới</h1>
          </v-container>
        </v-row>
        <v-col>
          <v-card elevation="2" light class="bg-wave rounded-0 px-4 py-4">
            <v-card-title class="px-3"> Đăng kí tài khoản </v-card-title>
            <v-form v-model="valid" name="form-login">
              <v-container>
                <v-row class="text-left">
                  <v-col cols="12" md="12">
                    <v-text-field v-model.trim="data.full_name" required type="text" label="Họ tên" flat
                      dense></v-text-field>
                    <p class="color-red" v-if="error && error.full_name">{{ error.full_name }}</p>
                  </v-col>
                  <v-col cols="12" md="12">
                    <v-text-field v-model.trim="data.email" required type="text" label="Email" flat dense></v-text-field>
                    <p class="color-red" v-if="error && error.email">{{ error.email }}</p>
                  </v-col>
                  <v-col cols="12" md="12">
                    <v-text-field v-model.trim="data.mobile" type="text" name="phone" label="Số điện thoại" flat
                      dense></v-text-field>
                    <p class="color-red" v-if="error && error.mobile">{{ error.mobile }}</p>
                  </v-col>
                  <v-col cols="12" md="12">
                    <v-text-field v-model.trim="data.password" type="password" name="password" label="Password" flat
                      dense></v-text-field>
                    <p class="color-red" v-if="error && error.password">{{ error.password }}</p>
                  </v-col>
                  <v-col cols="12" md="12">
                    <v-text-field v-model.trim="password_repeat" type="password" name="password_repeat" label="Nhập lại password"
                      flat dense></v-text-field>
                    <p class="color-red" v-if="error && error.password_repeat">{{ error.password_repeat }}</p>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-btn type="button" color="primary" @click="register">Đăng kí</v-btn>
                  </v-col>
                  <v-col cols="12" md="6" class="text-right">
                    <nuxt-link to="/login">Login</nuxt-link>
                    or
                    <nuxt-link to="/">Back</nuxt-link>
                  </v-col>
                </v-row>
              </v-container>
            </v-form>
          </v-card>
        </v-col>
      </v-flex>
    </div>
    <span><img src="/nuxt.png" height="12" /> Nuxtify &copy;
      {{ new Date().getFullYear() }} {{ company }}. Dark Mode:
      <code>{{ isDark }}</code></span>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import global from '~/constants/global'
import AuthService from '~/service/AuthService'
import AuthUtil from '~/utils/AuthUtil'
import LocalStorageHelper from '~/utils/LocalStorageHelper'
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE } from '~/utils/const'

export default {
  layout: 'blank',
  data() {
    return {
      data: {},
      password_repeat: '',
      company: global.company,
      error: [],
      valid: false
    }
  },
  computed: {
    ...mapState({
      isDark: (state) => state.core.theme.isDark
    })
  },
  methods: {
    async register() {
      this.error = []
      if(this.data.password && this.password_repeat != this.data.password){
        this.error['password_repeat'] = 'Nhắc lại mật khẩu chưa đúng!'
        return false;
      }
      const registerRes = await AuthService.register(this.data)
      if (registerRes) {
        if (registerRes.status == API_CODE.Succeed) {
          LocalStorageHelper.set('access_token', registerRes.data.access_token);
          AuthUtil.setCurrentUser(registerRes.data.user)
          return this.$router.push('/')
        } else {
          this.error = registerRes.data.errors
        }
      } else {
        JsCoreHelper.showErrorMsg("Lỗi hệ thống")
      }
    },
  }
}
</script>
