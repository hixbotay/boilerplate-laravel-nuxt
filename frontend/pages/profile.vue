<template>
  <v-row>
    <v-col class="text-center">
      <PageHeader title="Thông tin cá nhân" />
      <PaymentClientSummary />
      <v-row class="text-left">
        <v-col md="12" light>
          <v-card light>
            <v-card-title tag="h2">Thông tin tài khoản</v-card-title>
            <v-card-subtitle>Profile</v-card-subtitle>
            <v-card-text>
              <PaymentClientSummary/>
              <v-row>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.email" disabled label="Tài khoản"></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.mobile" label="Số điện thoại"></v-text-field>
                  <p v-if="error && error.mobile" class="text-error">{{ error.mobile }}</p>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.full_name" label="Tên chủ tài khoản"></v-text-field>
                  <p v-if="error && error.full_name" class="text-error">{{ error.full_name }}</p>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.password" type="password" label="Đổi Mật khẩu"></v-text-field>
                  <p v-if="error && error.password" type="password" class="text-error">{{ error.password }}</p>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-if="currentUser.role" v-model="currentUser.role.name" disabled label="Quyền hạn">
                  </v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.created_at" disabled label="Ngày tạo tài khoản"></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field v-model="currentUser.updated_at" disabled label="Cập nhật mới nhất"></v-text-field>
                </v-col>
              </v-row>
              <v-btn color="blue-darken-1" :disabled="loading" variant="text" @click="save">
                  Cập nhật
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-col>
  </v-row>
</template>

<script>
import PageHeader from '@/components/Header/PageHeader.vue'
import PaymentClientSummary from '~/components/Payment/PaymentClientSummary.vue';
import UserService from '~/service/UserService';
import AuthUtil from '~/utils/AuthUtil';
import { API_CODE } from '~/utils/const';


export default {
  name: 'ProfilePage',
  components: { PageHeader, PaymentClientSummary },
  data: () => ({
    currentUser: {},
    error: {},
    loading: false,
  }),
  created() {
    this.initilize()
  },
  methods: {
    initilize() {
      this.getCurrentUser();
    },
    async getCurrentUser() {
      this.currentUser = await AuthUtil.getCurrentUser(true);
    },
    async save() {
      if (!(this.currentUser.password && this.currentUser.password.length > 5)) {
        delete this.currentUser.password;
      }
        this.loading=true;
        const saveRes = await UserService.updateClient(this.currentUser);
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.currentUser = saveRes.data.user;
          AuthUtil.setCurrentUser(this.currentUser)
          this.loading=false;
        } else if(saveRes && saveRes.data.errors){
            this.error = saveRes.data.errors
          }
      }
  }
}
</script>
  