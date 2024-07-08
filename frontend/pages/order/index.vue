<template>
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <h2 class="title-page-sub">Quản lí đơn hàng</h2>
          <v-card>
            <v-card-title>
              <v-spacer></v-spacer>
              <v-text-field v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
                hide-details></v-text-field>
              <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
            </v-card-title>
         
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Mã giao dịch</th>
                    <th class="text-left">Email</th>
                    <th class="text-left">Số điện thoại</th>
                    <th class="text-left">Giá trị</th>
                    <th class="text-left">Trạng thái</th>
                    <th class="text-left">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item,index) in items" :key="item.id">
                    <td>{{ getTxid(item.payment_transaction) }}</td>
                    <td>{{ item.email }}</td>
                    <td>{{ item.phone }}</td>
                    <td>{{ item.total }} vnđ</td>
                    <td>{{ getPaymentStatus(item.payment_status) }}</td>
                    <td>{{ item.created_at }}</td>
                    <td>
                      <v-icon size="x-large" class="me-2" @click="editItem(index)"> mdi-alert-circle </v-icon>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
            <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>
  
            <v-dialog v-model="dialog">
              <v-card>
                <v-card-title>
                  <span class="text-h5">Thông tin hóa đơn</span>
                </v-card-title>
              </v-toolbar>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="6" md="4">
                      <span>Mã giao dịch:</span>
                      <span class="font_size_information">{{ getTxid(editedItem.payment_transaction) }}</span>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <span>Email:</span>
                      <span class="font_size_information">{{ editedItem.email }}</span>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <span>Số điện thoại:</span>
                      <span class="font_size_information">{{ editedItem.phone }}</span>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <span>Chi phí:</span>
                      <span class="font_size_information">{{ editedItem.total }} vnđ</span>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <span>Trạng thái:</span>
                      <span class="font_size_information">{{ getPaymentStatus(editedItem.payment_status) }}</span>
                    </v-col>
                    <v-col cols="12" sm="12" md="12">
                      <span>Sản phẩm {{ getNameProduct(editedItem.product) }}</span>
                      <v-simple-table>
                        <template v-slot:default>
                          <thead>
                            <tr>
                              <th class="text-left">Tên</th>
                              <th class="text-left">Thông tin</th>
                              <th class="text-left">Thời gian tạo</th>
                              <th class="text-left">Thời cập nhật</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(item) in editedItem.order_product_item" :key="item.id">
                              <td>{{ item.name }}</td>
                              <td>{{ item.description }}</td>
                              <td>{{ item.created_at }}</td>
                              <td>{{ item.updated_at }}</td>

                            </tr>
                          </tbody>
                        </template>
                      </v-simple-table>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-card>
      </v-col>
    </v-col>
  </v-row>
</template>
  
<script>
import OrderService from '~/service/OrderService'
import AuthUtil from '~/utils/AuthUtil';
import CommonUtil from '~/utils/CommonUtil';
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE, PAGING, PRODUCT_ITEM_STATUS } from '~/utils/const'

export default {
  name: 'Order',
  components: {},
  data: () => ({
    dialog: false,
    loading: false,
    dialogDelete: false,
    query: {
      keyword: '',
      page: 1,
      per_page: PAGING.PerPage
    },
    error: [],
    totalPage: 1,
    status: [
      { value: 1, state: 'Đang chờ giao dịch' },
      { value: 2, state: 'Đã duyệt' },
      { value: 3, state: 'Hủy bỏ' }
    ],
    items: [],
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},
    user: {}
  }),

  computed: {
  },
  watch: {
    'query.page'(_newVal, _oldVal) {
      this.getData()
    },
    'query.per_page'(_newVal, _oldVal) {
      this.getData()
      this.getPaging()
    }
  },

  created() {
    this.initilize();
    this.getCurrentUser();
  },

  methods: {
    initilize() {
      this.getData();
      this.getPaging();
    },
    async getData() {
      const orderRes = await OrderService.getClient(this.query)
      // console.log(orderRes);
      if (orderRes && orderRes.status == API_CODE.Succeed) {
        this.items = orderRes.data.records
      }
    },
    async getPaging() {
      const queryPaging = { ...this.query }
      queryPaging.is_paginate = 1
      const orderRes = await OrderService.getClient(queryPaging);
      if (orderRes && orderRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(orderRes.data.total_records / this.query.per_page)
      }
    },
    async getCurrentUser() {
      this.user = await AuthUtil.getCurrentUser();
    },
    async editItem(index) {
      this.dialog = true
      // eslint-disable-next-line eqeqeq
      if (index != undefined) {
        const orderRes = await OrderService.detaillient(this.items[index].id);
        if (orderRes && orderRes.status == API_CODE.Succeed) {
          this.editedItem = orderRes.data;
        }
        this.editedIndex = index;
      }
    },

    close() {
      this.loading = false
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    getUser(userObj) {
      if (userObj) {
        return userObj.email;
      }
      return null;
    },
    getPaymentStatus(statusNumber) {
      if (statusNumber === 1) {
        return 'Đang chờ giao dịch';
      }
      if (statusNumber === 2) {
        return 'Đã duyệt';
      }
      if (statusNumber === 3) {
        return 'Hủy bỏ';
      }
    },
    getTxid(payment) {
      if (payment) {
        return payment.tx_id;
      }
      return null;
    },
    getNameProduct(productObj) {
      if (Array.isArray(productObj)) {
        return productObj[0].name
      }
      return null;
    },
    displayStatus(enumVal) {
      return CommonUtil.getEnumDisplay(enumVal, PRODUCT_ITEM_STATUS)
    },
  }
}
</script>
  
<style lang="scss" scoped>
.font_size_information {
  font-size: 16px;
  display: block;
  color: #333;
}
</style>