<template>
  <v-row>
    <v-col class="text-center">
      <v-col class="text-left">
        <h2 class="title-page-sub">Lịch sử giao dịch</h2>
        <PaymentClientSummary/>
        <v-card>
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Mã giao dịch</th>
                  <th class="text-left">Số tiền</th>
                  <th class="text-left">Trạng thái</th>
                  <th class="text-left">Ngày tạo</th>
                  <th class="text-left">Ngày cập nhật</th>
                  <th class="text-left"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,index) in items" :key="item.id">
                  <td>{{ item.tx_id }}</td>
                  <td>{{item.order_id ? '-' : '+'}}{{ item.total }}</td>
                  <td>{{displayStatus(item.status) }}</td>
                  <td>{{ item.created_at }}</td>
                  <td>{{ item.updated_at }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
          <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>
        </v-card>
      </v-col>
    </v-col>
  </v-row>
</template>

<script>
import PaymentClientSummary from '~/components/Payment/PaymentClientSummary.vue'
import PaymentService from '~/service/PaymentService'
import CommonUtil from '~/utils/CommonUtil'
import { API_CODE, PAGING, PAY_STATUS } from '~/utils/const'

export default {
  name: 'PaymentHisotry',
  components: { PaymentClientSummary },
  data: () => ({
    dialog: false,
    loading: false,
    selected: [],
    middleware: ['auth'],
    dialogDelete: false,
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
      const settingRes = await PaymentService.getclientTransaction(this.query)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.items = settingRes.data.records
      }
    },
    async getPaging() {
      const queryPaging = {...this.query}
      queryPaging['is_paginate'] = 1
      const settingRes = await PaymentService.getclientTransaction(this.query)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(settingRes.data.total_records / this.query.per_page)
      }
    },

    displayStatus(enumVal){
      return CommonUtil.getEnumDisplay(enumVal,PAY_STATUS)
    },

    close() {
      this.loading=false
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = this.defaultItem
        this.editedIndex = -1
      })
    },
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
