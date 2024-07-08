<template>
  <v-card class="product-item-list">
    <div class="d-flex justify-center mt-1 product-image"><img v-if="item.image" :src="getImage(item.image)" /></div>
    <v-card-title class="d-flex justify-center">{{ item.name }}</v-card-title>
    <v-card-subtitle class="d-flex justify-center">Giá {{ displayMoney(item.price_sale ? item.price_sale : item.price)
    }}</v-card-subtitle>
    <v-card-subtitle class="d-flex justify-center">Số lượng còn lại {{ item.quantity }}</v-card-subtitle>
    <v-card-actions class="d-flex justify-center">
      <v-btn color="" @click="viewDetail()" class="mr-1">Thông tin</v-btn>
      <v-btn v-if="item.quantity > 0" color="primary" @click="buyItem(item)">Mua</v-btn>
      <v-btn v-else disabled>Hết hàng</v-btn>
    </v-card-actions>

    <v-dialog v-model="dialogProductDetail" max-width="800px">
      <v-card class="product-detail">
        <v-card-title class="text-h5">{{ item.name }}</v-card-title>
        <v-card-text class="">
          <Loading :loading="loading">
            <template v-if="item">
              <p>Tên: {{ item.name }}</p>
              <p>SKU: {{ item.sku }}</p>
              <p>Số lượng: {{ item.quantity }}</p>
              <p>Chi tiết sản phẩm</p>
              <div v-html="item.description"></div>
            </template>
          </Loading>
          <div class="d-flex">
            <v-text-field v-model="buyData.count_product" label="Số lượng" class="product-quantity" type="number"
              :rules="[rules.required, rules.number]"></v-text-field>
            <div>Giá {{ displayMoney(buyData.count_product * (item.price_sale ? item.price_sale : item.price)) }}</div>
          </div>
        </v-card-text>

        <v-card-actions>
          <div class="d-flex justify-center">
            <v-btn color="blue-darken-1" variant="text" @click="dialogBuy = false; dialogProductDetail = false">Đóng
              lại</v-btn>
            <v-btn color="primary" variant="text" :disabled="loading" @click="confirmBuy">Mua</v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>


    <v-dialog v-model="dialogBuy" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Chọn số lượng muốn mua <b class="ml-1">{{ item.name }}</b></v-card-title>
        <v-card-text class="d-flex">
          <v-text-field v-model="buyData.count_product" label="Số lượng" class="w-20" type="number"
            :rules="[rules.required, rules.number]"></v-text-field>
          <div>Giá {{ displayMoney(buyData.count_product * (item.price_sale ? item.price_sale :
            item.price)) }}</div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="dialogBuy = false">Đóng lại</v-btn>
          <v-btn color="primary" variant="text" :disabled="loading" @click="confirmBuy">Mua</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
import Loading from '~/components/Loading.vue'
import OrderService from '~/service/OrderService';
import ProductService from '~/service/ProductService';
import CommonUtil from '~/utils/CommonUtil';
import { API_CODE } from '~/utils/const';

export default {
  name: 'ClientProductItem',
  components: {
    Loading
  },
  props: {
    item: { type: Object, default: {} },
  },
  data() {
    return {
      rules: {
        required: (value) => !!value || 'Bắt buộc.',
        number: (value) => /^\d+$/.test(value) || 'Phải là số.',
      },
      buyData: { count_product: 1 },
      loading: true,
      dialogBuy: false,
      dialogProductDetail: false
    }
  },
  mounted() {
    this.selected = this.inputSelected
  },
  methods: {
    async viewDetail() {
      this.dialogProductDetail = true
    },
    getImage(input) {
      return CommonUtil.getImage(input)
    },
    buyItem(item) {
      this.item = item
      this.dialogBuy = true
    },
    async confirmBuy() {
      this.loading = true
      this.buyData['product_id'] = this.item.id

      const apiRes = await OrderService.buy(this.buyData);
      this.loading = false
      this.dialogBuy = false
      this.dialogProductDetail = false
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        return JsCoreHelper.showErrorMsg("Mua hàng thành công", 'info');
      }
      if (apiRes) {
        return JsCoreHelper.showErrorMsg(apiRes?.data?.message)
      }
      return JsCoreHelper.showErrorMsg("Có lỗi xảy ra, vui lòng thử lại!")
    },
    displayMoney(value) {
      return CommonUtil.displayMoney(value)
    },

  },
  watch: {
    inputSelected() {
      this.selected = this.inputSelected
    }
  }
}
</script>
