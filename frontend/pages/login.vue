<template>
  <div class="d-flex justify-center align-center min-h-100vh">
    <v-flex lg6>
      <v-row no-gutters>
        <v-container fluid class="pb-12">
          <h1 class="text-center color-white">Đăng nhập</h1>
        </v-container>
      </v-row>
      <v-col>
        <v-card elevation="2" light class="bg-wave">
          <v-card-title> Đăng nhập </v-card-title>
          <v-container class="text-error text-red" v-if="error">{{ error }}</v-container>
          <v-form v-model="valid" name="form-login" @submit="onSubmit()">
            <v-container>
              <v-row class="text-left">
                <v-col cols="12" md="12">
                  <v-text-field v-model.trim="data.email" type="text" label="Email" outlined dense></v-text-field>
                </v-col>
                <v-col cols="12" md="12">
                  <v-text-field v-model.trim="data.password" type="password" name="password" label="Password" outlined
                    dense></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-btn type="button" color="primary" @click="onSubmit" :disabled="loading">Đăng nhập</v-btn>
                </v-col>
                <v-col cols="12" md="6" class="text-right">
                  <nuxt-link to="/forgot-password">Quên mật khẩu</nuxt-link>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card>
      </v-col>
      <v-col>
        Chưa có tài khoản?
        <nuxt-link to="/register">Đăng kí</nuxt-link>
      </v-col>
    </v-flex>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import LocalStorageHelper from '~/utils/LocalStorageHelper'
import { API_CODE } from '~/utils/const'
import AuthService from '~/service/AuthService'
import JsCoreHelper from '~/utils/JsCoreHelper'
import AuthUtil from '~/utils/AuthUtil'

export default {
  layout: 'blank',
  data() {
    return {
      data: {},
      error: '',
      valid: false,
      loading: false
    }
  },
  computed: {
    ...mapState({
      isDark: (state) => state.core.theme.isDark
    })
  },
  methods: {
    async onSubmit() {
      this.loading=true
      const loginRes = await AuthService.login(this.data)
      this.loading=false
      if (loginRes) {
        if (loginRes.status == API_CODE.Succeed) {
          LocalStorageHelper.set('access_token', loginRes.data.access_token);
          LocalStorageHelper.setObject('user', loginRes.data.user);
          if(await AuthUtil.can('admin.list.orders')){
            return this.$router.push('/admin/')
          }else{
            return this.$router.push('/')
          }
          
        } else {
          this.error = loginRes.data.message
        }
      } else {
        this.error = "Lỗi hệ thống"
      }
      return false

    },
  }
}
</script>
