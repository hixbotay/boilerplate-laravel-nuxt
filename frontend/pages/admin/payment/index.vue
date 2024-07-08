<template>
  <v-row>
    <v-col class="text-center">
      <v-col class="text-left">
        <h2 class="title-page-sub">Quản lí/Phê duyệt giao dịch</h2>
        <v-card>
          <v-card-title>

            <v-spacer></v-spacer>
            <v-text-field v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
              hide-details></v-text-field>
            <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
          </v-card-title>
          <v-toolbar>
            <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm mới</v-btn>
            <v-divider class="mx-4" inset vertical></v-divider>
          </v-toolbar>
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Mã giao dịch</th>
                  <th class="text-left">
                    Số tiền
                    <v-btn
                      v-show="!isOrderBy('total', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('total', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('total', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('total', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left">Loại giao dịch</th>
                  <th class="text-left">Trạng thái</th>
                  <th class="text-left">Người dùng</th>
                  <th class="text-left">Thông tin</th>
                  <th class="text-left">
                    Ngày tạo
                    <v-btn
                      v-show="!isOrderBy('created_at', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('created_at', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('created_at', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('created_at', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left">Ngày cập nhật</th>
                  <th class="text-left"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,index) in items" :key="item.id">
                  <td>{{ item.tx_id }}</td>
                  <td>{{ item.total ? item.total.toLocaleString (): 0 }}</td>
                  <td>
                    <v-chip v-if="item.order_id > 0">Mua hàng</v-chip>
                    <v-chip v-else>Nạp tiền</v-chip>
                  </td>
                  <td>{{displayStatus(item.status) }}</td>
                  <td>{{item.user ? (item.user.full_name?item.user.full_name:item.user.email) : '' }}</td>
                  <td>{{item.data}}</td>
                  <td>{{ item.created_at }}</td>
                  <td>{{ item.updated_at }}</td>
                  <td class="d-flex">
                    <v-icon size="small" class="me-2" @click="editItem(index)"> mdi-pencil </v-icon>
                    <v-icon v-if="item.status == payStatus.waitting.value && item.order_id==0" size="small" class="me-2" @click="approvePayment(index)" label="Duyệt giao dịch"> mdi-check </v-icon>
                    <v-icon v-if="item.status == payStatus.waitting.value && item.order_id==0" size="small" class="me-2" @click="cancelPayment(index)" label="Hủy giao dịch"> mdi-close </v-icon>
                    <v-icon size="small" @click="deleteItem(index)"> mdi-delete </v-icon>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
          <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>

          <v-dialog v-model="dialog">
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="6" md="4">
                      <v-text-field v-model="editedItem.name" label="Key"></v-text-field>
                      <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <v-textarea v-model="editedItem.value" label="Giá trị"></v-textarea>
                      <p v-if="error && error.value" class="text-error">{{ error.value }}</p>
                    </v-col>
                    <v-row v-if="editedItem.id">
                      <v-col cols="12">
                        <p>Ngày tạo {{ editedItem.created_at }}</p>
                        <p>Ngày cập nhật {{ editedItem.updated_at }}</p>
                      </v-col>
                    </v-row>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="close">
                  Cancel
                </v-btn>
                <v-btn color="blue-darken-1" :disabled="loading" variant="text" @click="save">
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">Bạn có muốn xóa?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancel</v-btn>
                <v-btn color="primary" variant="text" @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogApprove" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">Phê duyệt giao dịch này, khách hàng {{ editedItem.user?editedItem.user.full_name:'' }} sẽ được cộng {{editedItem.total?editedItem.total.toLocaleString() : ''}}đ vào tài khoản?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="dialogApprove=false">Cancel</v-btn>
                <v-btn color="primary" variant="text" @click="approvePaymentConfirm">Duyệt</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogCancel" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">Hủy bỏ giao dịch {{editedItem.tx_id ? editedItem.tx_id : ''}}?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="dialogCancel=false">Cancel</v-btn>
                <v-btn color="primary" variant="text" @click="cancelPaymentConfirm">Duyệt</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-card>
      </v-col>
    </v-col>
  </v-row>
</template>

<script>
import Paging from '~/components/Paging.vue'
import PaymentService from '~/service/PaymentService'
import CommonUtil from '~/utils/CommonUtil'
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE, PAGING, PAY_STATUS } from '~/utils/const'

export default {
  name: 'AdminPayment',
  middleware: ['auth'],
  layout: 'admin',
  components: {  },
  data: () => ({
    dialog: false,
    payStatus: PAY_STATUS,
    loading: false,
    selected: [],
    dialogDelete: false,
    dialogApprove: false,
    dialogCancel: false,
    query: {
      keyword: '',
      page: 1,
      per_page: PAGING.PerPage
    },
    error: [],
    totalPage: 1,
    items: [
    ],
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
    },
  },

  created() {
    this.initilize()
  },

  methods: {
    initilize() {
      this.getData()
      this.getPaging()
    },
    async getData() {
      const settingRes = await PaymentService.get(this.query)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.items = settingRes.data.records
      }
    },
    async getPaging() {
      const queryPaging = {...this.query}
      queryPaging['is_paginate'] = 1
      const settingRes = await PaymentService.get(queryPaging)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(settingRes.data.total_records / this.query.per_page)
      }
    },

    editItem(index) {
      if(index!=undefined){
        this.editedItem = this.items[index]
        this.editedIndex = index
      }
      this.dialog = true
    },

    deleteItem(index) {
      this.editedIndex = index
      this.editedItem = this.items[index]
      this.dialogDelete = true
    },

    deleteItemConfirm() {
      const apiRes = PaymentService.destroy(this.editedItem.id)
      if (apiRes && apiRes == API_CODE.DeleteSucceed) {
        this.getData()

      }
      this.closeDelete()
    },

    close() {
      this.loading=false
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = this.defaultItem
        this.editedIndex = -1
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = this.defaultItem
        this.editedIndex = -1
      })
    },
    setOrderBy(orderBy, orderType) {
      this.query.order_by = orderBy;
      this.query.order_type = orderType;
      this.getData();
    },
    isOrderBy(orderBy, orderType) {
      if (this.query.order_by == orderBy && this.query.order_type == orderType) {
        return true;
      }
      return false;
    },
    async save() {
      this.loading=true
      if (this.editedIndex > -1) {
        const saveRes = await PaymentService.update(this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.items[this.editedIndex] = saveRes.data
          this.close()
        } else {
          if(saveRes&&saveRes.data.errors){
            this.error = saveRes.data.errors
          }
          JsCoreHelper.showErrorMsg(saveRes.data.message);
        }
      } else {
        const saveRes = await PaymentService.create(this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.items.push(saveRes.data)
          this.close()
        } else {
          if(saveRes&&saveRes.data.errors){
            this.error = saveRes.data.errors
          }
          JsCoreHelper.showErrorMsg(saveRes.data.message);
        }
      }
    },
    displayStatus(enumVal){
      return CommonUtil.getEnumDisplay(enumVal,PAY_STATUS)
    },
    approvePayment(index) {
      this.editedIndex = index
      this.editedItem = this.items[index]
      this.dialogApprove = true
    },
    async approvePaymentConfirm(){
      const apiRes = await PaymentService.approvePayment(this.editedItem.id);
      this.dialogApprove = false
      if(apiRes && apiRes.status == API_CODE.Succeed){
        this.items[this.editedIndex].status = apiRes.data.payment_transaction.status
        return JsCoreHelper.showErrorMsg("Phê duyệt thành công",'info');
      }
      if(apiRes){
        return JsCoreHelper.showErrorMsg(apiRes?.data?.message)
      }
      return JsCoreHelper.showErrorMsg("Có lỗi xảy ra, vui lòng thử lại!")
    },
    cancelPayment(index) {
      this.editedIndex = index
      this.editedItem = this.items[index]
      this.dialogCancel = true
    },
    async cancelPaymentConfirm(){
      const apiRes = await PaymentService.cancelPayment(this.editedItem.id);
      this.dialogCancel = false
      if(apiRes && apiRes.status == API_CODE.Succeed){
        this.items[this.editedIndex].status = apiRes.data.payment_transaction.status
        return JsCoreHelper.showErrorMsg("Hủy bỏ thành công",'info');
      }
      if(apiRes){
        return JsCoreHelper.showErrorMsg(apiRes?.data?.message)
      }
      return JsCoreHelper.showErrorMsg("Có lỗi xảy ra, vui lòng thử lại!")
    }
  },
  watch: {
    'query.page': function (newVal, oldVal) {
      this.getData()
    },
    'query.per_page': function (newVal, oldVal) {
      this.getData()
      this.getPaging()
    },
  }
}
</script>
