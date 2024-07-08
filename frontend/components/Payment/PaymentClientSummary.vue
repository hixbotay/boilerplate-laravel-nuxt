<template>
  <v-row class="text-left">
    <v-col md="4">
      <v-card>
        <v-card-title tag="h2">Số tiền hiện có</v-card-title>
        <v-card-text>{{ formatMoney(user.amount ? user.amount : 0) }}</v-card-text>
      </v-card>
    </v-col>
    <v-col md="4">
      <v-card>
        <v-card-title tag="h2">Số tiền đã chi tiêu</v-card-title>
        <v-card-text>{{ formatMoney(user.total_paid ? user.total_paid : 0) }}</v-card-text>
      </v-card>
    </v-col>
    <v-col md="4">
      <v-card>
        <v-card-title tag="h2">Số tiền đã nạp</v-card-title>
        <v-card-text>{{ formatMoney(user.total_purchased ? user.total_purchased : 0) }}</v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script>

import AuthUtil from '~/utils/AuthUtil';
import CommonUtil from '~/utils/CommonUtil';

export default {
  name: 'PaymentClientSummary',
  props: {
  },
  data() {
    return {
      user:{}
    }
  },
  async mounted(){
    this.user = await AuthUtil.getCurrentUser()
  },
  methods: {
    formatMoney(value){
      return CommonUtil.displayMoney(value)
    }
  },
}
</script>

