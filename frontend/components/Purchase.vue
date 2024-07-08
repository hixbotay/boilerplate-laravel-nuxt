<template>
  <div class="purchase">
    <v-btn small><v-icon class="mr-3">mdi-cash-100</v-icon> Số dư: {{ formatCurrency(currentUser.amount, 'VND') }}</v-btn>
    <v-btn small @click="dialog=true">Nạp tiền</v-btn>
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-toolbar color="primary" dark>
          <v-btn icon @click="dialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>Nạp tiền</v-toolbar-title>
        </v-toolbar>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <p v-if="errorMessage" class="text-error">{{ errorMessage }}</p>
                <div v-show="screen=='get_qrcode'" >
                  <v-text-field v-model="amount" label="Nhập vào số tiền bạn muốn nạp"></v-text-field>
                  <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                  <v-card-actions class="d-flex justify-center">
                    <v-btn color="primary" variant="text" :disabled="loading" @click="getQrCode">Tiếp theo</v-btn>
                  </v-card-actions>
                </div>
                <div v-show="screen=='confirm'">
                  <div><img :src="qr_code" class="d-block w-100"/></div>
                  <!-- <div>Nạp tiền theo thông tin như sau</div>
                  <div>Tên ngân hàng: {{ bank_name }}</div>
                  <div>Số tài khoản: {{ bank_user_name }}</div>
                  <div>Tên tài khoản: {{ bank_number }}</div>
                  <div>Chi nhánh: {{ bank_address}}</div>
                  <div>Số tiền: {{ amount.toLocaleString() }}đ</div>
                  <div>Nội dung: {{ tx_id }}</div> -->
                  <div>Sau khi nạp tiền bạn bấm xác nhận và chờ admin kiểm duyệt</div>
                  <div>(nếu trong vòng 2h số tiền của bạn chưa cập nhật thì vui lòng liên hệ đội ngũ admin)</div>
                  <v-card-actions class="d-flex justify-center">
                    <v-btn color="primary" variant="text" :disabled="loading" @click="confirmPurchase">Xác nhận nạp tiền</v-btn>
                  </v-card-actions>
                </div>
              </v-col>

            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogThankyou" max-width="500px">
      <v-card>
        <v-toolbar color="primary" dark>
          <v-btn icon @click="dialogThankyou = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-title class="text-h5">Giao dịch thành công</v-card-title>
        <v-card-actions class="d-flex justify-center">
          <v-btn color="primary" variant="text" @click="dialogThankyou = false">Đóng lại</v-btn>
          <v-spacer></v-spacer>
          <NuxtLink class="v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--default primary" to="/payment-history">Xem lịch sử giao dịch</NuxtLink>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

import PaymentService from '~/service/PaymentService';
import AuthUtil from '~/utils/AuthUtil';
import CommonUtil from '~/utils/CommonUtil';
import { API_CODE } from '~/utils/const';

export default {
  name: 'Purchase',
  props: {
  },
  data() {
    return {
      dialog: false,
      screen: 'get_qrcode',
      amount:50000,
      tx_id: '',
      bank_name: '',
      bank_user_name: '',
      bank_address: '',
      bank_number: '',
      loading: false,
      qr_code: '',
      error: {},
      dialogThankyou:false,
      errorMessage: '',
      currentUser: {}
    }
  },
  mounted() {
    this.getCurrentUser();
  },
  methods: {
    async getQrCode() {
      if (this.amount <= 0) {
        return
      }
      this.errorMessage = false
      this.loading=true
      const apiRes = await PaymentService.getQrcode({amount: this.amount})
      this.loading=false
      if(apiRes && apiRes.status==API_CODE.Succeed){
        this.screen = 'confirm'
        this.qr_code = apiRes.data.qr_code
        this.bank_name = apiRes.data.bank_name
        this.bank_address = apiRes.data.bank_address
        this.bank_number = apiRes.data.bank_number
        this.bank_user_name = apiRes.data.bank_user_name
        this.bank_number = apiRes.data.bank_number
        this.tx_id = apiRes.data.tx_id
      }
    },
    async confirmPurchase(){
      this.errorMessage = false
      this.loading=true
      const apiRes = await PaymentService.purchase({amount: this.amount,tx_id:this.tx_id})
      this.loading=false
      if(apiRes && apiRes.status==API_CODE.Succeed){
        this.screen = 'get_qrcode'
        this.dialog=false
        this.dialogThankyou=true
      }else{
        this.errorMessage = apiRes ? apiRes.data.message:'Lỗi hệ thống'
      }
    },
    async getCurrentUser() {
      this.currentUser = await AuthUtil.getCurrentUser(true);
    },
    formatCurrency(price, currency = 'USD') {
      return CommonUtil.formatCurrency(price, currency);
    }
  },
}
</script>
